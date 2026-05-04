<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Bpm;

use App\Services\Bpm\BpmCalculator;
use App\Services\Bpm\Dto\BpmInput;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class BpmCalculatorTest extends TestCase
{
    /**
     * Canonical test from plan.md §446–466: Volkswagen Golf 1.5 TSI Comfortline,
     * benzine, eerste toelating 15 april 2019, CO2 130 g/km, geëxporteerd 15 april 2026.
     * Expected: bruto BPM ≈ €7.318, afschrijving ≈ 81,5%, rest-BPM ≈ €1.354.
     */
    public function test_it_calculates_rest_bpm_for_a_seven_year_old_petrol_passenger_car(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2019, 4, 15),
            co2: 130,
            brandstof: 'Benzine',
        );

        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 15));

        $this->assertTrue($result->isEligible);
        $this->assertEqualsWithDelta(7318, $result->brutoBpm, 5);
        $this->assertEqualsWithDelta(81.5, $result->afschrijvingPercentage, 0.1);
        $this->assertEqualsWithDelta(1354, $result->restBpm, 5);
        $this->assertSame(84, $result->ageMonths);
        $this->assertSame('forfaitair', $result->method);
    }

    /**
     * Canonical test from plan.md §468–478: pre-16-okt-2006 → niet toelaatbaar.
     */
    public function test_it_refuses_refund_for_a_car_registered_before_16_october_2006(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2006, 10, 15),
            co2: 130,
            brandstof: 'Benzine',
        );

        $result = $calculator->calculateRestBpm($input);

        $this->assertFalse($result->isEligible);
        $this->assertNotNull($result->ineligibleReason);
        $this->assertSame(0.0, $result->restBpm);
    }

    public function test_a_car_registered_on_the_cutoff_date_is_eligible(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2006, 10, 16),
            co2: 130,
            brandstof: 'Benzine',
        );

        // We don't have 2006 tariffs configured — fallback applies, but still eligible.
        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertTrue($result->isEligible);
    }

    public function test_diesel_gets_surcharge_added_to_bruto_bpm(): void
    {
        $calculator = $this->makeCalculator();
        $exportDate = CarbonImmutable::create(2026, 4, 15);

        $petrol = new BpmInput(CarbonImmutable::create(2019, 4, 15), 130, 'Benzine');
        $diesel = new BpmInput(CarbonImmutable::create(2019, 4, 15), 130, 'Diesel');

        $petrolResult = $calculator->calculateRestBpm($petrol, $exportDate);
        $dieselResult = $calculator->calculateRestBpm($diesel, $exportDate);

        // Diesel surcharge 2019: (130 - 70) × €87,38 = €5.242,80 extra bruto.
        $this->assertEqualsWithDelta(5242.8, $dieselResult->brutoBpm - $petrolResult->brutoBpm, 1);
    }

    public function test_electric_uses_ev_fixed_amount_regardless_of_co2(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2019, 4, 15),
            co2: 0,
            brandstof: 'Elektriciteit',
        );

        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 15));

        // 2019 ev_fixed = 0 → bruto en rest beide 0.
        $this->assertTrue($result->isEligible);
        $this->assertSame(0.0, $result->brutoBpm);
        $this->assertSame(0.0, $result->restBpm);
    }

    public function test_unknown_year_falls_back_to_nearest_year_with_a_note(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2022, 1, 1),
            co2: 130,
            brandstof: 'Benzine',
        );

        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertTrue($result->isEligible);
        $this->assertNotEmpty($result->notes);
        $this->assertStringContainsString('2022', $result->notes[0]);
    }

    public function test_depreciation_table_matches_plan_examples(): void
    {
        $calculator = $this->makeCalculator();

        // 84 months → 79% + (84-78)*0.417 = 81.502%
        $input84 = new BpmInput(CarbonImmutable::create(2019, 4, 15), 130, 'Benzine');
        $result84 = $calculator->calculateRestBpm($input84, CarbonImmutable::create(2026, 4, 15));
        $this->assertEqualsWithDelta(81.5, $result84->afschrijvingPercentage, 0.1);

        // 1 month → 0%
        $input1 = new BpmInput(CarbonImmutable::create(2024, 12, 1), 130, 'Benzine');
        $result1 = $calculator->calculateRestBpm($input1, CarbonImmutable::create(2025, 1, 1));
        $this->assertSame(0.0, $result1->afschrijvingPercentage);

        // 25+ years → 100% (no rest-BPM)
        $inputOld = new BpmInput(CarbonImmutable::create(2007, 1, 1), 130, 'Benzine');
        $resultOld = $calculator->calculateRestBpm($inputOld, CarbonImmutable::create(2050, 1, 1));
        $this->assertSame(100.0, $resultOld->afschrijvingPercentage);
        $this->assertSame(0.0, $resultOld->restBpm);
    }

    private function makeCalculator(): BpmCalculator
    {
        return new BpmCalculator(config('bpm_rates'));
    }
}
