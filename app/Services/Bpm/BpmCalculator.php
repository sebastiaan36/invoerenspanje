<?php

declare(strict_types=1);

namespace App\Services\Bpm;

use App\Services\Bpm\Dto\BpmInput;
use App\Services\Bpm\Dto\BpmResult;
use Carbon\CarbonImmutable;
use RuntimeException;

final class BpmCalculator
{
    /**
     * @param  array{
     *     eligibility_cutoff_date: string,
     *     depreciation_table: list<array{
     *         max_months: int,
     *         base_months: int,
     *         base_percentage: float|int,
     *         per_month: float|int,
     *     }>,
     *     periods: list<array{
     *         from: string,
     *         to: string|null,
     *         fixed_base: float|int,
     *         brackets: list<array{max: int|null, rate: float|int}>,
     *         diesel_fixed_base?: float|int,
     *         diesel_brackets?: list<array{max: int|null, rate: float|int}>,
     *         phev_fixed_base?: float|int,
     *         phev_brackets?: list<array{max: int|null, rate: float|int}>,
     *         bestelauto_fixed_base?: float|int,
     *         bestelauto_brackets?: list<array{max: int|null, rate: float|int}>,
     *         diesel: array{threshold: int, rate_per_gram: float|int},
     *         ev_fixed: float|int,
     *     }>,
     * }  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function calculateRestBpm(BpmInput $input, ?CarbonImmutable $exportDate = null): BpmResult
    {
        $exportDate ??= CarbonImmutable::now();
        $cutoff = CarbonImmutable::parse($this->config['eligibility_cutoff_date']);

        if ($input->datumEersteToelating->lt($cutoff)) {
            return BpmResult::notEligible(
                "Datum eerste toelating ligt vóór {$cutoff->format('d-m-Y')}; BPM-teruggave is dan niet mogelijk.",
            );
        }

        if ($input->historischBrutoBpm !== null) {
            // The RDW knows the actual historische bruto BPM for most vehicles.
            // That is the exact teruggave-base, so prefer it over reconstructing
            // the amount from CO2 tariff tables.
            $brutoBpm = (float) $input->historischBrutoBpm;
            $notes = [];
        } elseif ($this->isCommercial($input->voertuigsoort)) {
            [$brutoBpm, $notes] = $this->calculateBestelautoBruto($input);
        } else {
            [$rates, $notes] = $this->resolveRates($input->datumEersteToelating);
            $brutoBpm = $this->calculateBrutoBpm($input, $rates);
        }

        $months = $this->calculateAgeInMonths($input->datumEersteToelating, $exportDate);
        $afschrijving = $this->getDepreciationPercentage($months);

        $restBpm = $brutoBpm * (100 - $afschrijving) / 100;

        return BpmResult::eligible(
            brutoBpm: round($brutoBpm, 2),
            afschrijvingPercentage: round($afschrijving, 3),
            ageMonths: $months,
            restBpm: round(max(0.0, $restBpm), 2),
            notes: $notes,
        );
    }

    /**
     * Find the tariff period that contains the registration date. When no
     * period matches (e.g. a pre-2013 price-based car, or a date beyond the
     * configured periods) we fall back to the nearest period and attach a
     * warning so the indication is never presented as exact.
     *
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    private function resolveRates(CarbonImmutable $date): array
    {
        $periods = $this->config['periods'];

        if ($periods === []) {
            throw new RuntimeException('Geen BPM-tarieven geconfigureerd.');
        }

        $match = $this->findPeriodForDate($date);
        if ($match !== null) {
            return [$match, []];
        }

        $earliest = $periods[0];
        $earliestFrom = CarbonImmutable::parse($earliest['from']);

        if ($date->lt($earliestFrom)) {
            // Pre-2013: BPM was based on the net list price, not CO2. The
            // CO2-based table cannot reproduce this, so we only give a rough
            // indication on the oldest available tariff and warn heavily.
            return [$earliest, [
                'Let op: deze auto is van vóór '.$earliestFrom->year.'. Tot '.$earliestFrom->year.' werd de '
                .'BPM berekend over de netto-catalogusprijs en niet over de CO₂-uitstoot. Deze indicatie is '
                .'een ruwe benadering op basis van de tarieven van '.$earliestFrom->year.' en kan sterk '
                .'afwijken van de werkelijke BPM. Gebruik deze niet voor de aangifte — vraag een offerte aan '
                .'voor een exacte berekening.',
            ]];
        }

        // Date beyond the newest configured period: use the most recent tariff.
        $latest = $periods[count($periods) - 1];

        return [$latest, [
            'Voor dit bouwjaar zijn de BPM-tarieven nog niet bevestigd. Indicatie op basis van de meest '
            .'recente bekende tarieven.',
        ]];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findPeriodForDate(CarbonImmutable $date): ?array
    {
        foreach ($this->config['periods'] as $period) {
            $from = CarbonImmutable::parse($period['from']);
            $to = $period['to'] !== null ? CarbonImmutable::parse($period['to']) : null;

            if ($date->gte($from) && ($to === null || $date->lte($to))) {
                return $period;
            }
        }

        return null;
    }

    /**
     * Bestelauto's (voertuigsoort 'Bedrijfsauto') have their own regime: a flat
     * amount per gram CO2 from 2025 onwards. Before 2025 the BPM was based on the
     * net list price (37,7%), which we cannot derive from the RDW CO2 data — so we
     * give a rough indication on the oldest CO2-based bestelauto tariff and warn.
     *
     * @return array{0: float, 1: list<string>}
     */
    private function calculateBestelautoBruto(BpmInput $input): array
    {
        $period = $this->findPeriodForDate($input->datumEersteToelating);

        if ($period !== null && isset($period['bestelauto_brackets'])) {
            return [$this->bestelautoBpm($input->co2, $period), []];
        }

        $fallback = $this->oldestBestelautoPeriod();

        if ($fallback === null) {
            return [0.0, [
                'De BPM voor bestelauto’s van dit bouwjaar kunnen wij niet automatisch berekenen. '
                .'Vraag een offerte aan voor een exacte berekening.',
            ]];
        }

        $fallbackYear = CarbonImmutable::parse($fallback['from'])->year;

        return [$this->bestelautoBpm($input->co2, $fallback), [
            'Let op: tot '.$fallbackYear.' werd de BPM voor bestelauto’s berekend over de '
            .'netto-catalogusprijs (37,7%) en niet over de CO₂-uitstoot. Deze indicatie is een ruwe '
            .'benadering op basis van het bestelautotarief van '.$fallbackYear.' en kan sterk afwijken '
            .'van de werkelijke BPM. Gebruik deze niet voor de aangifte — vraag een offerte aan voor een '
            .'exacte berekening.',
        ]];
    }

    /**
     * @param  array<string, mixed>  $period
     */
    private function bestelautoBpm(int $co2, array $period): float
    {
        return (float) ($period['bestelauto_fixed_base'] ?? 0)
            + $this->calculateCo2Component($co2, $period['bestelauto_brackets']);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function oldestBestelautoPeriod(): ?array
    {
        foreach ($this->config['periods'] as $period) {
            if (isset($period['bestelauto_brackets'])) {
                return $period;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $rates
     */
    private function calculateBrutoBpm(BpmInput $input, array $rates): float
    {
        if ($this->isElectric($input->brandstof)) {
            return (float) $rates['ev_fixed'];
        }

        $isDiesel = $this->isDiesel($input->brandstof);

        if ($input->isPlugin && isset($rates['phev_brackets'])) {
            // PHEV had its own (steeper) table over the weighted CO2 in 2017–2024.
            $bpm = (float) ($rates['phev_fixed_base'] ?? 0);
            $bpm += $this->calculateCo2Component($input->co2, $rates['phev_brackets']);
        } elseif ($isDiesel && isset($rates['diesel_brackets'])) {
            // Some years (2013/2014) define a separate diesel schijventabel.
            $bpm = (float) ($rates['diesel_fixed_base'] ?? 0);
            $bpm += $this->calculateCo2Component($input->co2, $rates['diesel_brackets']);
        } else {
            $bpm = (float) $rates['fixed_base'];
            $bpm += $this->calculateCo2Component($input->co2, $rates['brackets']);
        }

        if ($isDiesel) {
            $bpm += $this->calculateDieselToeslag($input->co2, $rates['diesel']);
        }

        return $bpm;
    }

    /**
     * @param  list<array{max: int|null, rate: float|int}>  $brackets
     */
    private function calculateCo2Component(int $co2, array $brackets): float
    {
        $bpm = 0.0;
        $previousLimit = 0;

        foreach ($brackets as $bracket) {
            $limit = $bracket['max'] ?? PHP_INT_MAX;
            $effectiveCo2 = min($co2, $limit);
            $gramsInBracket = max(0, $effectiveCo2 - $previousLimit);
            $bpm += $gramsInBracket * $bracket['rate'];

            if ($co2 <= $limit) {
                break;
            }
            $previousLimit = $limit;
        }

        return $bpm;
    }

    /**
     * @param  array{threshold: int, rate_per_gram: float|int}  $dieselConfig
     */
    private function calculateDieselToeslag(int $co2, array $dieselConfig): float
    {
        $excess = max(0, $co2 - $dieselConfig['threshold']);

        return $excess * $dieselConfig['rate_per_gram'];
    }

    /**
     * Belastingdienst-regel: einde-maand-tot-einde-maand telt als hele maand.
     * Carbon's diffInMonths past dit toe (truncates towards zero).
     */
    private function calculateAgeInMonths(CarbonImmutable $start, CarbonImmutable $end): int
    {
        return (int) floor($start->diffInMonths($end));
    }

    private function getDepreciationPercentage(int $months): float
    {
        foreach ($this->config['depreciation_table'] as $tier) {
            if ($months <= $tier['max_months']) {
                $extraMonths = max(0, $months - $tier['base_months']);

                return (float) $tier['base_percentage'] + ($extraMonths * (float) $tier['per_month']);
            }
        }

        return 100.0; // Buiten de tabel: 25+ jaar, geen restwaarde.
    }

    private function isDiesel(string $brandstof): bool
    {
        return strtolower($brandstof) === 'diesel';
    }

    private function isElectric(string $brandstof): bool
    {
        return in_array(strtolower($brandstof), ['elektriciteit', 'elektrisch'], true);
    }

    private function isCommercial(?string $voertuigsoort): bool
    {
        return $voertuigsoort !== null && str_contains(strtolower($voertuigsoort), 'bedrijfsauto');
    }
}
