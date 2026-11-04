<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Bpm;

use App\Services\Bpm\BpmCalculator;
use App\Services\Bpm\Dto\BpmInput;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class BpmCalculatorTest extends TestCase
{
    /**
     * Officiële Belastingdienst-tarieven 2019: een benzine personenauto met
     * 130 g/km CO2 heeft een bruto BPM van € 360 + 71×€2 + 24×€60 + 35×€131 = € 6.527.
     * Eerste toelating 15 april 2019, geëxporteerd 15 april 2026 (84 maanden,
     * afschrijving 72% + 6 × 0,25% = 73,5% volgens de tabel van 1 juli 2023).
     * Vermindering 6527 × 73,5% = 4797,35 → naar beneden afgerond € 4.797; rest € 1.730.
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
        $this->assertEqualsWithDelta(73.5, $result->afschrijvingPercentage, 0.001);
        $this->assertSame(1730.0, $result->restBpm);
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

        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2020, 4, 4));

        $this->assertTrue($result->isEligible);
        $this->assertSame(3562.0, $result->brutoBpm);
        $this->assertEmpty($result->notes); // exact figure → no rough-indication warning
        // 156 months → 81% + 42 × 0,19% = 88,98% → vermindering € 3.169 → rest € 393.
        $this->assertSame(393.0, $result->restBpm);
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

    public function test_a_refund_below_50_euro_is_not_paid_out(): void
    {
        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2010, 1, 1),
            co2: 130,
            brandstof: 'Benzine',
            historischBrutoBpm: 1350,
        );

        // 195 (begonnen) maanden → 96,39% afgeschreven → vermindering € 1.301 → rest € 49.
        $result = $this->makeCalculator()->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 1));

        $this->assertFalse($result->isEligible);
        $this->assertSame(0.0, $result->restBpm);
        $this->assertSame(49.0, $result->calculatedRestBpm);
        $this->assertSame(1350.0, $result->brutoBpm);
        $this->assertStringContainsString('lager dan € 50', (string) $result->ineligibleReason);
    }

    public function test_a_refund_of_exactly_50_euro_is_paid_out(): void
    {
        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2010, 1, 1),
            co2: 130,
            brandstof: 'Benzine',
            historischBrutoBpm: 1360,
        );

        // 96,39% van € 1.360 = € 1.310,90 → vermindering € 1.310 → rest € 50.
        $result = $this->makeCalculator()->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 1));

        $this->assertTrue($result->isEligible);
        $this->assertSame(50.0, $result->restBpm);
    }

    public function test_a_car_without_bpm_gets_a_clear_reason(): void
    {
        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2019, 4, 15),
            co2: 130,
            brandstof: 'Benzine',
            historischBrutoBpm: 0,
        );

        $result = $this->makeCalculator()->calculateRestBpm($input, CarbonImmutable::create(2026, 4, 15));

        $this->assertFalse($result->isEligible);
        $this->assertStringContainsString('geen BPM betaald', (string) $result->ineligibleReason);
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

        // No pre-2013 tariffs configured — fallback applies; the car is not refused for its age.
        $result = $calculator->calculateRestBpm($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertGreaterThan(0.0, $result->brutoBpm);
        $this->assertStringNotContainsString('vóór 16-10-2006', (string) $result->ineligibleReason);
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

        // 84 months → 72% + (84-78) × 0,25% = 73,5%
        $input84 = new BpmInput(CarbonImmutable::create(2019, 4, 15), 130, 'Benzine');
        $result84 = $calculator->calculateRestBpm($input84, CarbonImmutable::create(2026, 4, 15));
        $this->assertEqualsWithDelta(73.5, $result84->afschrijvingPercentage, 0.001);

        // Exported on the day of first admission → 0%
        $input0 = new BpmInput(CarbonImmutable::create(2024, 12, 1), 130, 'Benzine');
        $result0 = $calculator->calculateRestBpm($input0, CarbonImmutable::create(2024, 12, 1));
        $this->assertSame(0.0, $result0->afschrijvingPercentage);

        // First (begun) month → 12%
        $result1 = $calculator->calculateRestBpm($input0, CarbonImmutable::create(2024, 12, 2));
        $this->assertSame(12.0, $result1->afschrijvingPercentage);

        // 25+ years → 100% (no rest-BPM)
        $inputOld = new BpmInput(CarbonImmutable::create(2007, 1, 1), 130, 'Benzine');
        $resultOld = $calculator->calculateRestBpm($inputOld, CarbonImmutable::create(2050, 1, 1));
        $this->assertSame(100.0, $resultOld->afschrijvingPercentage);
        $this->assertSame(0.0, $resultOld->restBpm);
    }

    /**
     * Art. 8d UR BPM: every month that has passed "geheel of gedeeltelijk"
     * counts. Whole months follow the end-of-month rule (31 jan → 29 feb = 1).
     */
    #[DataProvider('monthBoundaries')]
    public function test_age_in_months_follows_the_end_of_month_rule(string $firstAdmission, string $export, int $expectedMonths): void
    {
        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::parse($firstAdmission),
            co2: 130,
            brandstof: 'Benzine',
        );

        $result = $this->makeCalculator()->calculateRestBpm($input, CarbonImmutable::parse($export));

        $this->assertSame($expectedMonths, $result->ageMonths);
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function monthBoundaries(): array
    {
        return [
            'same day next month' => ['2020-01-15', '2020-02-15', 1],
            'one day into the first month' => ['2020-01-15', '2020-01-16', 1],
            'one day short of a month' => ['2020-01-15', '2020-02-14', 1],
            'one day into the second month' => ['2020-01-15', '2020-02-16', 2],
            'end of month into leap February' => ['2020-01-31', '2020-02-29', 1],
            'one day before end of leap February' => ['2020-01-31', '2020-02-28', 1],
            'end of March to end of April' => ['2020-03-31', '2020-04-30', 1],
            'one day short of two months' => ['2020-01-31', '2020-03-30', 2],
            'seven years' => ['2019-04-15', '2026-04-15', 84],
            'seven years minus a day' => ['2019-04-15', '2026-04-14', 84],
            'seven years plus a day' => ['2019-04-15', '2026-04-16', 85],
            'export before admission' => ['2026-04-15', '2026-04-01', 0],
        ];
    }

    /**
     * Reference amounts from the VWE "BPM calculator teruggaaf bij export"
     * (diensten.vwe.nl), export date 4 November 2026, for cars that were new in
     * the Netherlands (first admission = first Dutch registration, so the BPM
     * paid equals the RDW bruto BPM).
     */
    #[DataProvider('vweReferenceCases')]
    public function test_rest_bpm_matches_the_vwe_export_calculator(string $firstAdmission, int $brutoBpm, float $expectedRest): void
    {
        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::parse($firstAdmission),
            co2: 130,
            brandstof: 'Benzine',
            historischBrutoBpm: $brutoBpm,
            datumEersteTenaamstellingNl: CarbonImmutable::parse($firstAdmission),
        );

        $result = $this->makeCalculator()->calculateRestBpm($input, CarbonImmutable::create(2026, 11, 4));

        $this->assertSame($expectedRest, $result->restBpm);
        $this->assertEmpty($result->notes);
    }

    /**
     * @return array<string, array{string, int, float}>
     */
    public static function vweReferenceCases(): array
    {
        return [
            'JZP41F 9 months' => ['2026-03-02', 3089, 2070.0],
            'JHN92D 13 months' => ['2025-10-15', 4293, 2705.0],
            'HLG63L 19 months' => ['2025-04-15', 91843, 52581.0],
            'S798LJ 45 months' => ['2023-03-01', 5027, 2099.0],
            'P595FJ 57 months' => ['2022-03-01', 10813, 3973.0],
            'K042ST 69 months' => ['2021-03-01', 3410, 1083.0],
            'XN620D 93 months' => ['2019-03-01', 4038, 980.0],
            'NK947N 117 months' => ['2017-03-01', 2023, 373.0],
            '7ZNJ76 141 months' => ['2015-03-01', 4334, 602.0],
            '06TJN1 177 months' => ['2012-03-01', 4408, 310.0],
            '08LLL8 198 months' => ['2010-06-01', 7117, 217.0],
        ];
    }

    public function test_a_used_import_gets_a_note_about_the_bpm_actually_paid(): void
    {
        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2021, 5, 10),
            co2: 130,
            brandstof: 'Benzine',
            historischBrutoBpm: 4907,
            datumEersteTenaamstellingNl: CarbonImmutable::create(2021, 11, 12),
        );

        $result = $this->makeCalculator()->calculateRestBpm($input, CarbonImmutable::create(2026, 11, 4));

        $this->assertTrue($input->isImported());
        $this->assertCount(1, $result->notes);
        $this->assertStringContainsString('geïmporteerd', $result->notes[0]);
    }

    public function test_a_car_registered_in_nl_within_a_month_is_not_an_import(): void
    {
        $input = new BpmInput(
            datumEersteToelating: CarbonImmutable::create(2024, 3, 1),
            co2: 130,
            brandstof: 'Benzine',
            historischBrutoBpm: 2792,
            datumEersteTenaamstellingNl: CarbonImmutable::create(2024, 3, 20),
        );

        $this->assertFalse($input->isImported());
    }

    private function makeCalculator(): BpmCalculator
    {
        return new BpmCalculator(config('bpm_rates'));
    }
}
