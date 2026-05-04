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
     *     years: array<int, array{
     *         fixed_base: float|int,
     *         brackets: list<array{max: int|null, rate: float|int}>,
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

        $year = $input->datumEersteToelating->year;
        $rates = $this->config['years'][$year] ?? null;

        $notes = [];

        if ($rates === null) {
            // Fallback: use the closest available year so the user gets *some* indication.
            $availableYears = array_keys($this->config['years']);
            if ($availableYears === []) {
                throw new RuntimeException('Geen BPM-tarieven geconfigureerd.');
            }
            usort($availableYears, fn (int $a, int $b) => abs($a - $year) <=> abs($b - $year));
            $fallbackYear = $availableYears[0];
            $rates = $this->config['years'][$fallbackYear];
            $notes[] = "Tarieven voor bouwjaar {$year} zijn nog niet bevestigd. Indicatie op basis van tarieven {$fallbackYear}.";
        }

        $brutoBpm = $this->calculateBrutoBpm($input, $rates);

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
     * @param  array{
     *     fixed_base: float|int,
     *     brackets: list<array{max: int|null, rate: float|int}>,
     *     diesel: array{threshold: int, rate_per_gram: float|int},
     *     ev_fixed: float|int,
     * }  $rates
     */
    private function calculateBrutoBpm(BpmInput $input, array $rates): float
    {
        if ($this->isElectric($input->brandstof)) {
            return (float) $rates['ev_fixed'];
        }

        $bpm = (float) $rates['fixed_base'];
        $bpm += $this->calculateCo2Component($input->co2, $rates['brackets']);

        if ($this->isDiesel($input->brandstof)) {
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
}
