<?php

declare(strict_types=1);

namespace App\Services\Bpm;

use App\Services\Bpm\Dto\BpmIndication;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Carbon\CarbonImmutable;

final class BpmCalculator
{
    /**
     * @param  array{
     *     disclaimer: string,
     *     rates: array<string, array{
     *         co2_threshold_g_per_km: int,
     *         fixed_voet_eur: int,
     *         per_gram_above_threshold_eur: int,
     *         diesel_surcharge_per_gram_eur: float,
     *         diesel_surcharge_threshold_g_per_km: int,
     *     }>,
     *     depreciation_table: list<array{int, int, float}>,
     *     diesel_minimum_residual_pct: float,
     * }  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function calculate(VehicleLookupResult $result, ?CarbonImmutable $now = null): ?BpmIndication
    {
        if (! $result->found() || $result->vehicle === null || $result->fuel === null) {
            return null;
        }

        $vehicle = $result->vehicle;
        $fuel = $result->fuel;
        $now ??= CarbonImmutable::now();

        if ($vehicle->datumEersteToelating === null) {
            return null;
        }

        $registrationYear = $vehicle->datumEersteToelating->year;
        $rate = $this->rateFor($registrationYear);
        $fuelType = $this->normalizeFuelType($fuel->brandstofOmschrijving);
        $co2 = $fuel->co2UitstootGecombineerd ?? $fuel->co2UitstootGewogen ?? 0;

        $originalBpm = $this->originalBpm($rate, $fuelType, $co2);
        $ageMonths = (int) floor($vehicle->datumEersteToelating->diffInMonths($now));
        $depreciationPct = $this->depreciationPct($ageMonths);

        $residualPct = max(0.0, 100.0 - $depreciationPct);

        if ($fuelType === 'diesel') {
            $residualPct = max($residualPct, $this->config['diesel_minimum_residual_pct']);
        }

        $refund = (int) round($originalBpm * ($residualPct / 100.0));

        return new BpmIndication(
            estimatedRefundEur: max(0, $refund),
            originalBpmEur: (int) round($originalBpm),
            depreciationFactor: $depreciationPct / 100.0,
            inputs: [
                'kenteken' => $vehicle->kenteken,
                'registratiejaar' => $registrationYear,
                'leeftijd_maanden' => $ageMonths,
                'brandstof' => $fuelType,
                'co2_g_per_km' => $co2,
            ],
            notes: [
                $this->config['disclaimer'],
                "Berekening op basis van placeholder-tarieven (registratiejaar {$registrationYear}).",
            ],
        );
    }

    /**
     * @param  array{
     *     co2_threshold_g_per_km: int,
     *     fixed_voet_eur: int,
     *     per_gram_above_threshold_eur: int,
     *     diesel_surcharge_per_gram_eur: float,
     *     diesel_surcharge_threshold_g_per_km: int,
     * }  $rate
     */
    private function originalBpm(array $rate, string $fuelType, int $co2): float
    {
        $bpm = (float) $rate['fixed_voet_eur'];

        $aboveThreshold = max(0, $co2 - $rate['co2_threshold_g_per_km']);
        $bpm += $aboveThreshold * $rate['per_gram_above_threshold_eur'];

        if ($fuelType === 'diesel') {
            $aboveDieselThreshold = max(0, $co2 - $rate['diesel_surcharge_threshold_g_per_km']);
            $bpm += $aboveDieselThreshold * $rate['diesel_surcharge_per_gram_eur'];
        }

        return $bpm;
    }

    private function depreciationPct(int $ageMonths): float
    {
        foreach ($this->config['depreciation_table'] as [$min, $max, $pct]) {
            if ($ageMonths >= $min && $ageMonths < $max) {
                return $pct;
            }
        }

        // Beyond the table → use the last (oldest) bracket.
        $last = end($this->config['depreciation_table']);

        return is_array($last) ? (float) $last[2] : 92.0;
    }

    /**
     * @return array{
     *     co2_threshold_g_per_km: int,
     *     fixed_voet_eur: int,
     *     per_gram_above_threshold_eur: int,
     *     diesel_surcharge_per_gram_eur: float,
     *     diesel_surcharge_threshold_g_per_km: int,
     * }
     */
    private function rateFor(int $year): array
    {
        return $this->config['rates'][(string) $year] ?? $this->config['rates']['default'];
    }

    private function normalizeFuelType(?string $description): string
    {
        return match (strtolower($description ?? '')) {
            'diesel' => 'diesel',
            'elektriciteit', 'elektrisch' => 'electric',
            'lpg', 'lpg/gas' => 'lpg',
            default => 'gasoline',
        };
    }
}
