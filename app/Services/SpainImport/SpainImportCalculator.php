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
     *     fixed_costs: list<array{key: string, label: string, amount_eur: float|int}>,
     *     market_value_depreciation_table: list<array{int, int, float|int}>,
     *     exemptions: array{residency_change: array{reason: string}},
     *     notes: array{commercial_vehicle: string},
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

        $marketValue = $this->estimateMarketValue($input, $importDate);
        $rate = $this->iedmtRate($autonomiaConfig['iedmt_brackets'], $input->co2);

        $exempt = $input->residencyChange;
        $exemptReason = $exempt
            ? $this->config['exemptions']['residency_change']['reason']
            : null;

        $iedmt = $exempt ? 0.0 : round($marketValue * ($rate / 100), 2);

        $fixedCosts = $this->fixedCosts();
        $fixedCostsTotal = array_sum(array_map(fn (FixedCostItem $c) => $c->amountEur, $fixedCosts));

        $notes = [];
        if ($this->isCommercialVehicle($input->voertuigsoort)) {
            $notes[] = $this->config['notes']['commercial_vehicle'];
        }

        return new SpainImportResult(
            iedmtEur: $iedmt,
            iedmtRatePct: $rate,
            iedmtExempt: $exempt,
            iedmtExemptReason: $exemptReason,
            estimatedMarketValueEur: round($marketValue, 2),
            fixedCosts: $fixedCosts,
            fixedCostsTotalEur: round($fixedCostsTotal, 2),
            totalEur: round($iedmt + $fixedCostsTotal, 2),
            autonomia: $input->autonomia,
            notes: $notes,
        );
    }

    private function estimateMarketValue(SpainImportInput $input, CarbonImmutable $importDate): float
    {
        if ($input->catalogusprijsEur === null || $input->catalogusprijsEur <= 0) {
            return 0.0;
        }

        $ageYears = (int) floor($input->datumEersteToelating->diffInYears($importDate));
        $depreciationPct = $this->depreciationPct($ageYears);

        return $input->catalogusprijsEur * (1 - $depreciationPct / 100);
    }

    private function depreciationPct(int $ageYears): float
    {
        foreach ($this->config['market_value_depreciation_table'] as [$min, $max, $pct]) {
            if ($ageYears >= $min && $ageYears < $max) {
                return (float) $pct;
            }
        }

        return 90.0;
    }

    /**
     * @param  list<array{co2_max_g_per_km: int|null, rate_pct: float|int}>  $brackets
     */
    private function iedmtRate(array $brackets, int $co2): float
    {
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
            fn (array $c) => new FixedCostItem($c['key'], $c['label'], (float) $c['amount_eur']),
            $this->config['fixed_costs'],
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
