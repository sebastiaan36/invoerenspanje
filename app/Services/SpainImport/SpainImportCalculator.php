<?php

declare(strict_types=1);

namespace App\Services\SpainImport;

use App\Services\SpainImport\Dto\FixedCostItem;
use App\Services\SpainImport\Dto\SpainImportInput;
use App\Services\SpainImport\Dto\SpainImportResult;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

final class SpainImportCalculator
{
    /**
     * @param  array{
     *     autonomias: array<string, array{
     *         label: string,
     *         iedmt_brackets: list<array{co2_max_g_per_km: int|null, rate_pct: float|int}>,
     *     }>,
     *     fixed_costs: list<array{key: string, label: string, amount_eur?: float|int, amount_excl_vat_eur?: float|int, vat_pct?: float|int, optional?: bool}>,
     *     hacienda: array{table_year: int, apply_foreign_registration_correction: bool, match_cache_ttl_seconds?: int},
     *     hacienda_retention_pct_by_full_years: list<int>,
     *     iva_rate_history: list<array{0: string, 1: float|int}>,
     *     exemptions: array{residency_change: array{reason: string}},
     *     static_notes: list<string>,
     *     notes: array{commercial_vehicle: string, unknown_co2: string, iedmt_unknown_value: string, iedmt_unknown_co2: string, hacienda_table_missing: string, catalogue_price_fallback: string},
     * }  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function calculate(SpainImportInput $input, ?CarbonImmutable $importDate = null): SpainImportResult
    {
        $importDate ??= CarbonImmutable::now();

        $autonomiaConfig = $this->config['autonomias'][$input->autonomia]
            ?? throw new InvalidArgumentException("Onbekende autonome regio: {$input->autonomia}");

        [$newValue, $valueSource] = $this->newValue($input);
        $retentionPct = $this->retentionPct($input->datumEersteToelating, $importDate);
        $marketValue = $newValue * $retentionPct / 100;

        $rate = $this->iedmtRate($autonomiaConfig['iedmt_brackets'], $input);
        $taxableValue = $marketValue / $this->foreignRegistrationDivisor($input->datumEersteToelating, $rate ?? 0.0);

        $exempt = $input->residencyChange;
        $exemptReason = $exempt
            ? $this->config['exemptions']['residency_change']['reason']
            : null;

        $unknownReason = $exempt ? null : $this->undeterminableReason($rate, $valueSource);
        $iedmt = match (true) {
            $exempt => 0.0,
            $unknownReason !== null => null,
            default => round($taxableValue * ($rate / 100), 2),
        };

        $fixedCosts = $this->fixedCosts();
        $fixedCostsTotal = array_sum(array_map(
            fn (FixedCostItem $c): float => $c->optional ? 0.0 : $c->amountEur,
            $fixedCosts,
        ));

        $notes = $this->config['static_notes'] ?? [];
        if ($this->isCommercialVehicle($input->voertuigsoort)) {
            $notes[] = $this->config['notes']['commercial_vehicle'];
        }
        if ($unknownReason !== null) {
            $notes[] = $this->config['notes'][$unknownReason];
        }
        if (! $exempt && $rate !== null && $input->co2 === null && ! $input->isZeroEmission) {
            $notes[] = $this->config['notes']['unknown_co2'];
        }
        if (! $exempt && $rate !== null && $rate > 0 && $valueSource === SpainImportResult::VALUE_SOURCE_CATALOGUE_PRICE) {
            $notes[] = $input->haciendaTableAvailable
                ? $this->config['notes']['catalogue_price_fallback']
                : $this->config['notes']['hacienda_table_missing'];
        }

        return new SpainImportResult(
            iedmtEur: $iedmt,
            iedmtRatePct: $rate,
            iedmtExempt: $exempt,
            iedmtExemptReason: $exemptReason,
            estimatedMarketValueEur: round($marketValue, 2),
            fixedCosts: $fixedCosts,
            fixedCostsTotalEur: round($fixedCostsTotal, 2),
            totalEur: $iedmt === null ? null : round($iedmt + $fixedCostsTotal, 2),
            autonomia: $input->autonomia,
            notes: $notes,
            valueSource: $valueSource,
            newValueEur: (float) $newValue,
            haciendaModel: $input->hacienda?->description(),
            haciendaConfidence: $input->hacienda?->confidence,
            retentionPct: $retentionPct,
            taxableValueEur: round($taxableValue, 2),
            iedmtDeterminable: $iedmt !== null,
        );
    }

    /**
     * The key of the note explaining why the IEDMT cannot be calculated, or
     * null when it can. A 0% bracket needs no value: it is always € 0.
     */
    private function undeterminableReason(?float $rate, string $valueSource): ?string
    {
        if ($rate === null) {
            return 'iedmt_unknown_co2';
        }

        if ($rate > 0 && $valueSource === SpainImportResult::VALUE_SOURCE_NONE) {
            return 'iedmt_unknown_value';
        }

        return null;
    }

    /**
     * Step 1: the Hacienda table value, else the Dutch catalogue price when new.
     *
     * @return array{0: int, 1: string}
     */
    private function newValue(SpainImportInput $input): array
    {
        if ($input->hacienda !== null && $input->hacienda->newValueEur > 0) {
            return [$input->hacienda->newValueEur, SpainImportResult::VALUE_SOURCE_HACIENDA];
        }

        if ($input->catalogusprijsEur !== null && $input->catalogusprijsEur > 0) {
            return [$input->catalogusprijsEur, SpainImportResult::VALUE_SOURCE_CATALOGUE_PRICE];
        }

        return [0, SpainImportResult::VALUE_SOURCE_NONE];
    }

    /**
     * Step 2 (Anexo IV): the share of the new value left after the full years of use.
     * The percentage changes on the anniversary of the first registration.
     */
    private function retentionPct(CarbonImmutable $firstRegistration, CarbonImmutable $importDate): int
    {
        $table = $this->config['hacienda_retention_pct_by_full_years'];
        $fullYears = max(0, (int) floor($firstRegistration->diffInYears($importDate)));

        return (int) $table[min($fullYears, count($table) - 1)];
    }

    /**
     * Step 3 (art. 5, regel 2): strip the indirect taxes still hidden in the
     * market value of a car previously registered abroad.
     */
    private function foreignRegistrationDivisor(CarbonImmutable $firstRegistration, float $iedmtRatePct): float
    {
        if (! ($this->config['hacienda']['apply_foreign_registration_correction'] ?? false)) {
            return 1.0;
        }

        return 1 + $this->ivaRatePct($firstRegistration) / 100 + $iedmtRatePct / 100;
    }

    private function ivaRatePct(CarbonImmutable $firstRegistration): float
    {
        $rate = 21.0;

        foreach ($this->config['iva_rate_history'] as [$from, $pct]) {
            if ($firstRegistration->greaterThanOrEqualTo(CarbonImmutable::parse($from))) {
                $rate = (float) $pct;
            }
        }

        return $rate;
    }

    /**
     * Step 4: electric and hydrogen cars pay 0%; without a known CO2 value
     * Hacienda applies the highest bracket. When the RDW has no fuel data at
     * all we cannot tell the fuel type either, so the rate is unknown (null).
     *
     * @param  list<array{co2_max_g_per_km: int|null, rate_pct: float|int}>  $brackets
     */
    private function iedmtRate(array $brackets, SpainImportInput $input): ?float
    {
        if ($input->isZeroEmission) {
            return 0.0;
        }

        if ($input->fuelDataMissing) {
            return null;
        }

        $co2 = $input->co2;
        if ($co2 === null) {
            return (float) end($brackets)['rate_pct'];
        }

        foreach ($brackets as $bracket) {
            $max = $bracket['co2_max_g_per_km'];
            if ($max === null || $co2 <= $max) {
                return (float) $bracket['rate_pct'];
            }
        }

        // Should be unreachable if config has an open-ended last bracket.
        return (float) end($brackets)['rate_pct'];
    }

    /**
     * @return list<FixedCostItem>
     */
    private function fixedCosts(): array
    {
        return array_map(
            fn (array $c): FixedCostItem => new FixedCostItem($c['key'], $c['label'], $c['amount_eur'], $c['optional']),
            SpainFixedCosts::resolve($this->config['fixed_costs']),
        );
    }

    private function isCommercialVehicle(?string $voertuigsoort): bool
    {
        if ($voertuigsoort === null) {
            return false;
        }

        return str_contains(strtolower($voertuigsoort), 'bedrijfsauto');
    }
}
