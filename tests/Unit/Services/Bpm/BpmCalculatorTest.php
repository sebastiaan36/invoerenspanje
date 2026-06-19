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
     * Officiële Belastingdienst-tarieven 2019: een benzine personenauto met
     * 130 g/km CO2 heeft een bruto BPM van € 360 + 71×€2 + 24×€60 + 35×€131 = € 6.527.
     * Eerste toelating 15 april 2019, geëxporteerd 15 april 2026 (84 maanden,
     * afschrijving 81,5%).
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
        $this->assertEqualsWithDelta(6527, $result->brutoBpm, 1);
        $this->assertEqualsWithDelta(81.5, $result->afschrijvingPercentage, 0.1);
        $this->assertEqualsWithDelta(1207, $result->restBpm, 2);
        $this->assertSame(84, $result->ageMonths);
        $this->assertSame('forfaitair', $result->method);
        $this->assertEmpty($result->notes);
    }

    /**
     * The RDW exposes the actual historische bruto BPM for most vehicles. When
     * present, it is the exact teruggave-base and overrides the CO2 reconstruction
     * — even for a pre-2013 car that would otherwise only get a rough indication.
     */
    public function test_it_prefers_the_rdw_historische_bruto_bpm(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2007, 4, 4),
            co2: 155,
            brandstof: 'Benzine',
            historischBrutoBpm: 3562,
        );

        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 4));

        $this->assertTrue($result->isEligible);
        $this->assertSame(3562.0, $result->brutoBpm);
        $this->assertEmpty($result->notes); // exact figure → no rough-indication warning
        // 228 months → 96,902% afschrijving → rest ≈ 3562 × 0,03098 = € 110,35.
        $this->assertEqualsWithDelta(110.35, $result->restBpm, 0.5);
    }

    public function test_a_zero_rdw_bruto_bpm_is_respected(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2019, 4, 15),
            co2: 130,
            brandstof: 'Benzine',
            historischBrutoBpm: 0,
        );

        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 15));

        // RDW says € 0 bruto (e.g. exempt) → no teruggave, not a CO2 fallback.
        $this->assertSame(0.0, $result->brutoBpm);
        $this->assertSame(0.0, $result->restBpm);
    }

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

        // No pre-2013 tariffs configured — fallback applies, but still eligible.
        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertTrue($result->isEligible);
    }

    /**
     * Pre-2013 cars were taxed on net list price, not CO2. We fall back to the
     * oldest (2013) tariff and must attach a heavy warning. A 2007 petrol car
     * with 155 g/km gets the 2013 bruto BPM (€ 0 + 45×€125 + 15×€148 = € 7.845),
     * NOT the inflated value the old single-year config produced.
     */
    public function test_pre_2013_car_falls_back_to_oldest_tariff_with_heavy_warning(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2007, 4, 1),
            co2: 155,
            brandstof: 'Benzine',
        );

        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 1));

        $this->assertTrue($result->isEligible);
        $this->assertEqualsWithDelta(7845, $result->brutoBpm, 1);
        $this->assertNotEmpty($result->notes);
        $this->assertStringContainsString('vóór 2013', $result->notes[0]);
        $this->assertStringContainsString('catalogusprijs', $result->notes[0]);
    }

    public function test_diesel_gets_its_year_specific_surcharge_added(): void
    {
        $calculator = $this->makeCalculator();
        $exportDate = CarbonImmutable::create(2026, 4, 15);

        $petrol = new BpmInput(CarbonImmutable::create(2019, 4, 15), 130, 'Benzine');
        $diesel = new BpmInput(CarbonImmutable::create(2019, 4, 15), 130, 'Diesel');

        $petrolResult = $calculator->calculateRestBpm($petrol, $exportDate);
        $dieselResult = $calculator->calculateRestBpm($diesel, $exportDate);

        // Dieseltoeslag 2019: (130 - 61) × € 88,43 = € 6.101,67 extra bruto.
        $this->assertEqualsWithDelta(6101.67, $dieselResult->brutoBpm - $petrolResult->brutoBpm, 0.5);
    }

    /**
     * 2013 and 2014 have a separate diesel schijventabel (different bracket
     * limits than petrol) on top of the dieseltoeslag.
     */
    public function test_2013_diesel_uses_its_own_bracket_table(): void
    {
        $calculator = $this->makeCalculator();

        $diesel = new BpmInput(CarbonImmutable::create(2013, 6, 1), 130, 'Diesel');

        $result = $calculator->calculateRestBpm($diesel, CarbonImmutable::create(2026, 6, 1));

        // Diesel brackets: 42×€125 = € 5.250, plus dieseltoeslag (130-70)×€56,13 = € 3.367,80.
        $this->assertEqualsWithDelta(8617.8, $result->brutoBpm, 0.5);
    }

    /**
     * 2020 has two tariff tables: NEDC until 30 June, WLTP from 1 July. Same
     * CO2 must yield a different bruto BPM depending on the registration date.
     */
    public function test_2020_split_uses_the_correct_half_year_tariff(): void
    {
        $calculator = $this->makeCalculator();
        $exportDate = CarbonImmutable::create(2026, 1, 1);

        $firstHalf = new BpmInput(CarbonImmutable::create(2020, 3, 1), 130, 'Benzine');
        $secondHalf = new BpmInput(CarbonImmutable::create(2020, 9, 1), 130, 'Benzine');

        $firstResult = $calculator->calculateRestBpm($firstHalf, $exportDate);
        $secondResult = $calculator->calculateRestBpm($secondHalf, $exportDate);

        $this->assertEqualsWithDelta(6890, $firstResult->brutoBpm, 1);
        $this->assertEqualsWithDelta(3674, $secondResult->brutoBpm, 1);
        $this->assertEmpty($firstResult->notes);
        $this->assertEmpty($secondResult->notes);
    }

    public function test_electric_is_exempt_before_2025_but_pays_fixed_amount_from_2025(): void
    {
        $calculator = $this->makeCalculator();

        $before = new BpmInput(CarbonImmutable::create(2019, 4, 15), 0, 'Elektriciteit');
        $beforeResult = $calculator->calculateRestBpm($before, CarbonImmutable::create(2026, 4, 15));
        $this->assertSame(0.0, $beforeResult->brutoBpm);
        $this->assertSame(0.0, $beforeResult->restBpm);

        $from2025 = new BpmInput(CarbonImmutable::create(2025, 1, 1), 0, 'Elektriciteit');
        $from2025Result = $calculator->calculateRestBpm($from2025, CarbonImmutable::create(2026, 1, 1));
        $this->assertSame(667.0, $from2025Result->brutoBpm);
        $this->assertGreaterThan(0.0, $from2025Result->restBpm);
    }

    /**
     * A PHEV (2017–2024) is taxed over its low weighted CO2 via a separate,
     * steeper table. At 40 g/km a 2019 PHEV pays € 0 + 30×€27 + 10×€113 = € 1.940,
     * far more than the € 440 a normal car would pay at the same CO2.
     */
    public function test_phev_uses_its_own_steeper_table(): void
    {
        $calculator = $this->makeCalculator();
        $exportDate = CarbonImmutable::create(2026, 4, 15);

        $phev = new BpmInput(CarbonImmutable::create(2019, 4, 15), 40, 'Benzine', isPlugin: true);
        $normal = new BpmInput(CarbonImmutable::create(2019, 4, 15), 40, 'Benzine');

        $phevResult = $calculator->calculateRestBpm($phev, $exportDate);
        $normalResult = $calculator->calculateRestBpm($normal, $exportDate);

        $this->assertEqualsWithDelta(1940, $phevResult->brutoBpm, 1);
        $this->assertEqualsWithDelta(440, $normalResult->brutoBpm, 1);
    }

    /**
     * From 2025 the separate PHEV tariff is gone — a plug-in hybrid falls back
     * to the normal personenauto table.
     */
    public function test_phev_from_2025_uses_the_normal_table(): void
    {
        $calculator = $this->makeCalculator();

        $phev = new BpmInput(CarbonImmutable::create(2025, 1, 1), 40, 'Benzine', isPlugin: true);
        $result = $calculator->calculateRestBpm($phev, CarbonImmutable::create(2026, 1, 1));

        // Normal 2025 table: € 667 + 40×€2 = € 747.
        $this->assertEqualsWithDelta(747, $result->brutoBpm, 1);
    }

    public function test_2024_petrol_matches_official_tariff(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(CarbonImmutable::create(2024, 1, 15), 145, 'Benzine');

        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 1, 15));

        // € 440 + 80×€2 + 24×€76 + 41×€167 = € 9.271 (official kolom III bij 145 g/km).
        $this->assertEqualsWithDelta(9271, $result->brutoBpm, 1);
    }

    /**
     * Bestelauto's (voertuigsoort 'Bedrijfsauto') are CO2-based from 2025: a flat
     * € 74,41 per gram from 0 g/km, regardless of fuel (no dieseltoeslag).
     */
    public function test_bestelauto_from_2025_uses_the_flat_co2_rate(): void
    {
        $calculator = $this->makeCalculator();
        $exportDate = CarbonImmutable::create(2026, 1, 15);

        $petrol = new BpmInput(CarbonImmutable::create(2025, 1, 15), 150, 'Benzine', voertuigsoort: 'Bedrijfsauto');
        $diesel = new BpmInput(CarbonImmutable::create(2025, 1, 15), 150, 'Diesel', voertuigsoort: 'Bedrijfsauto');

        $petrolResult = $calculator->calculateRestBpm($petrol, $exportDate);
        $dieselResult = $calculator->calculateRestBpm($diesel, $exportDate);

        // 150 × € 74,41 = € 11.161,50 — no dieseltoeslag for bestelauto's.
        $this->assertEqualsWithDelta(11161.5, $petrolResult->brutoBpm, 0.5);
        $this->assertSame($petrolResult->brutoBpm, $dieselResult->brutoBpm);
        $this->assertEmpty($petrolResult->notes);
    }

    public function test_bestelauto_2026_uses_its_own_rate(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(CarbonImmutable::create(2026, 1, 15), 150, 'Benzine', voertuigsoort: 'Bedrijfsauto');
        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 6, 1));

        // 150 × € 76,57 = € 11.485,50.
        $this->assertEqualsWithDelta(11485.5, $result->brutoBpm, 0.5);
    }

    public function test_an_electric_bestelauto_pays_no_bpm(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(CarbonImmutable::create(2025, 1, 15), 0, 'Elektriciteit', voertuigsoort: 'Bedrijfsauto');
        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 1, 15));

        $this->assertSame(0.0, $result->brutoBpm);
    }

    /**
     * A pre-2025 bestelauto was taxed on net list price (37,7%), not CO2. We give
     * a rough indication on the oldest (2025) bestelauto tariff with a heavy warning.
     */
    public function test_pre_2025_bestelauto_gives_a_rough_indication_with_warning(): void
    {
        $calculator = $this->makeCalculator();

        $input = new BpmInput(CarbonImmutable::create(2019, 4, 15), 180, 'Diesel', voertuigsoort: 'Bedrijfsauto');
        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 15));

        // Rough indication on 2025 rate: 180 × € 74,41 = € 13.393,80.
        $this->assertEqualsWithDelta(13393.8, $result->brutoBpm, 0.5);
        $this->assertNotEmpty($result->notes);
        $this->assertStringContainsString('bestelauto', $result->notes[0]);
        $this->assertStringContainsString('37,7%', $result->notes[0]);
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
