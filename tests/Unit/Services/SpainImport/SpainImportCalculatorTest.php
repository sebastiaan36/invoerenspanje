<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SpainImport;

use App\Services\Hacienda\Dto\HaciendaMatch;
use App\Services\SpainImport\Dto\SpainImportInput;
use App\Services\SpainImport\Dto\SpainImportResult;
use App\Services\SpainImport\SpainImportCalculator;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class SpainImportCalculatorTest extends TestCase
{
    public function test_iedmt_is_zero_below_120_g_per_km(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 119);

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(0.0, $result->iedmtEur);
        $this->assertSame(0.0, $result->iedmtRatePct);
    }

    public function test_iedmt_uses_4_75_pct_between_120_and_159(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 130, catalogusprijs: 30000);

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(4.75, $result->iedmtRatePct);
        $this->assertGreaterThan(0.0, $result->iedmtEur);
    }

    public function test_iedmt_uses_9_75_pct_between_160_and_199(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 180, catalogusprijs: 30000);

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(9.75, $result->iedmtRatePct);
    }

    public function test_iedmt_uses_14_75_pct_above_200(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 250, catalogusprijs: 30000);

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(14.75, $result->iedmtRatePct);
    }

    public function test_residency_change_exempts_iedmt_but_keeps_fixed_costs(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 250, catalogusprijs: 30000, residencyChange: true);

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertTrue($result->iedmtExempt);
        $this->assertSame(0.0, $result->iedmtEur);
        $this->assertNotNull($result->iedmtExemptReason);
        $this->assertGreaterThan(0.0, $result->fixedCostsTotalEur);
        $this->assertSame($result->fixedCostsTotalEur, $result->totalEur);
    }

    public function test_market_value_depreciates_with_age(): void
    {
        $calculator = $this->makeCalculator();
        $now = CarbonImmutable::create(2025, 1, 1);

        $young = $this->input(co2: 130, catalogusprijs: 30000, datum: CarbonImmutable::create(2024, 1, 1));
        $old = $this->input(co2: 130, catalogusprijs: 30000, datum: CarbonImmutable::create(2015, 1, 1));

        $youngResult = $calculator->calculate($young, $now);
        $oldResult = $calculator->calculate($old, $now);

        $this->assertGreaterThan($oldResult->estimatedMarketValueEur, $youngResult->estimatedMarketValueEur);
        $this->assertGreaterThan($oldResult->iedmtEur, $youngResult->iedmtEur);
    }

    public function test_total_equals_iedmt_plus_fixed_costs(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 130, catalogusprijs: 30000);

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertEqualsWithDelta(
            $result->iedmtEur + $result->fixedCostsTotalEur,
            $result->totalEur,
            0.01,
        );
    }

    public function test_commercial_vehicle_gets_classification_note(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 100, voertuigsoort: 'Bedrijfsauto');

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertTrue(
            collect($result->notes)->contains(fn (string $note) => str_contains(strtolower($note), 'bedrijfsauto')),
            'Expected a note mentioning bedrijfsauto for a commercial vehicle.',
        );
    }

    public function test_passenger_car_does_not_get_commercial_note(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 100, voertuigsoort: 'Personenauto');

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertFalse(
            collect($result->notes)->contains(fn (string $note) => str_contains(strtolower($note), 'bedrijfsauto')),
            'Passenger car should not get the commercial vehicle note.',
        );
    }

    public function test_unknown_autonomia_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 100, autonomia: 'narnia');

        $calculator->calculate($input);
    }

    public function test_zero_catalogusprijs_yields_zero_iedmt(): void
    {
        // Without a Hacienda value and without a catalogue price the IEDMT is
        // unknown — never € 0 — when the car falls in a taxed bracket.
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 250, catalogusprijs: null);

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(0.0, $result->estimatedMarketValueEur);
        $this->assertNull($result->iedmtEur);
        $this->assertFalse($result->iedmtDeterminable);
        $this->assertSame(14.75, $result->iedmtRatePct);
        $this->assertNull($result->totalEur);
        $this->assertTrue(collect($result->notes)->contains(config('spain_import.notes.iedmt_unknown_value')));
        // Fixed costs still apply.
        $this->assertGreaterThan(0.0, $result->fixedCostsTotalEur);
    }

    public function test_unknown_new_value_is_irrelevant_in_the_zero_percent_bracket(): void
    {
        $result = $this->makeCalculator()->calculate($this->input(co2: 110, catalogusprijs: null), CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(0.0, $result->iedmtEur);
        $this->assertTrue($result->iedmtDeterminable);
        $this->assertSame($result->fixedCostsTotalEur, $result->totalEur);
        $this->assertFalse(collect($result->notes)->contains(config('spain_import.notes.iedmt_unknown_value')));
    }

    public function test_unknown_new_value_is_irrelevant_when_exempt(): void
    {
        $result = $this->makeCalculator()->calculate(
            $this->input(co2: 250, catalogusprijs: null, residencyChange: true),
            CarbonImmutable::create(2025, 1, 1),
        );

        $this->assertSame(0.0, $result->iedmtEur);
        $this->assertSame($result->fixedCostsTotalEur, $result->totalEur);
    }

    public function test_missing_fuel_data_makes_the_iedmt_not_determinable(): void
    {
        $input = new SpainImportInput(
            datumEersteToelating: CarbonImmutable::create(2020, 1, 1),
            co2: null,
            catalogusprijsEur: 30000,
            voertuigsoort: 'Personenauto',
            fuelDataMissing: true,
        );

        $result = $this->makeCalculator()->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertNull($result->iedmtRatePct);
        $this->assertNull($result->iedmtEur);
        $this->assertNull($result->totalEur);
        $this->assertFalse($result->iedmtDeterminable);
        $this->assertTrue(collect($result->notes)->contains(config('spain_import.notes.iedmt_unknown_co2')));
        // Never claim the highest bracket when we simply have no data.
        $this->assertFalse(collect($result->notes)->contains(config('spain_import.notes.unknown_co2')));
    }

    public function test_a_missing_hacienda_table_gets_its_own_note(): void
    {
        $input = new SpainImportInput(
            datumEersteToelating: CarbonImmutable::create(2020, 1, 1),
            co2: 130,
            catalogusprijsEur: 30000,
            voertuigsoort: 'Personenauto',
            haciendaTableAvailable: false,
        );

        $result = $this->makeCalculator()->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(SpainImportResult::VALUE_SOURCE_CATALOGUE_PRICE, $result->valueSource);
        $this->assertTrue(collect($result->notes)->contains(config('spain_import.notes.hacienda_table_missing')));
        $this->assertFalse(collect($result->notes)->contains(config('spain_import.notes.catalogue_price_fallback')));
    }

    public function test_iedmt_is_calculated_over_the_market_value_after_depreciation(): void
    {
        // VW Golf 2.0 TDI, eerste toelating 14-05-2019, inschrijving 20-11-2026,
        // Hacienda-nieuwwaarde € 26.000 × 28% = € 7.280 marktwaarde, CO2 128 g/km → IEDMT € 345,80.
        $input = $this->input(
            co2: 128,
            datum: CarbonImmutable::create(2019, 5, 14),
            hacienda: $this->haciendaMatch(26000),
        );

        $result = $this->makeCalculator()->calculate($input, CarbonImmutable::create(2026, 11, 20));

        $this->assertSame(SpainImportResult::VALUE_SOURCE_HACIENDA, $result->valueSource);
        $this->assertSame(28, $result->retentionPct);
        $this->assertSame(7280.0, $result->estimatedMarketValueEur);
        $this->assertSame(7280.0, $result->taxableValueEur);
        $this->assertSame(4.75, $result->iedmtRatePct);
        $this->assertSame(345.80, $result->iedmtEur);
    }

    public function test_the_optional_foreign_registration_correction_can_be_switched_on(): void
    {
        $result = $this->calculatorWithCorrection()->calculate(
            $this->input(co2: 128, datum: CarbonImmutable::create(2019, 5, 14), hacienda: $this->haciendaMatch(26000)),
            CarbonImmutable::create(2026, 11, 20),
        );

        // € 7.280 ÷ (1 + 0,21 + 0,0475) = € 5.789,26 → IEDMT € 274,99 (rekenvoorbeeld uitleg-PDF).
        $this->assertSame(5789.26, $result->taxableValueEur);
        $this->assertSame(274.99, $result->iedmtEur);
    }

    public function test_the_retention_percentage_changes_on_the_anniversary_of_first_registration(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 130, datum: CarbonImmutable::create(2024, 6, 10), hacienda: $this->haciendaMatch(20000));

        $dayBefore = $calculator->calculate($input, CarbonImmutable::create(2026, 6, 9));
        $onAnniversary = $calculator->calculate($input, CarbonImmutable::create(2026, 6, 10));

        $this->assertSame(84, $dayBefore->retentionPct);
        $this->assertSame(67, $onAnniversary->retentionPct);
    }

    public function test_cars_older_than_twelve_years_keep_ten_percent(): void
    {
        $input = $this->input(co2: 130, datum: CarbonImmutable::create(2001, 1, 1), hacienda: $this->haciendaMatch(30000));

        $result = $this->makeCalculator()->calculate($input, CarbonImmutable::create(2026, 11, 1));

        $this->assertSame(10, $result->retentionPct);
        $this->assertSame(3000.0, $result->estimatedMarketValueEur);
    }

    public function test_the_correction_uses_the_spanish_vat_rate_at_first_registration(): void
    {
        // 2011 → 18% IVA: 10% van 20.000 = 2.000 ÷ (1 + 0,18 + 0,0475).
        $input = $this->input(co2: 130, datum: CarbonImmutable::create(2011, 3, 1), hacienda: $this->haciendaMatch(20000));

        $result = $this->calculatorWithCorrection()->calculate($input, CarbonImmutable::create(2026, 11, 1));

        $this->assertSame(round(2000 / 1.2275, 2), $result->taxableValueEur);
    }

    public function test_unknown_co2_gets_the_highest_rate_and_a_note(): void
    {
        $result = $this->makeCalculator()->calculate($this->input(co2: null), CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(14.75, $result->iedmtRatePct);
        $this->assertTrue(collect($result->notes)->contains(config('spain_import.notes.unknown_co2')));
    }

    public function test_electric_cars_pay_no_iedmt(): void
    {
        $input = new SpainImportInput(
            datumEersteToelating: CarbonImmutable::create(2023, 1, 1),
            co2: null,
            catalogusprijsEur: 50000,
            voertuigsoort: 'Personenauto',
            isZeroEmission: true,
        );

        $result = $this->makeCalculator()->calculate($input, CarbonImmutable::create(2025, 6, 1));

        $this->assertSame(0.0, $result->iedmtRatePct);
        $this->assertSame(0.0, $result->iedmtEur);
        $this->assertFalse(collect($result->notes)->contains(config('spain_import.notes.unknown_co2')));
    }

    public function test_catalogue_price_is_the_fallback_when_hacienda_has_no_value(): void
    {
        $result = $this->makeCalculator()->calculate($this->input(co2: 130, catalogusprijs: 30000), CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(SpainImportResult::VALUE_SOURCE_CATALOGUE_PRICE, $result->valueSource);
        $this->assertSame(30000.0, $result->newValueEur);
        $this->assertTrue(collect($result->notes)->contains(config('spain_import.notes.catalogue_price_fallback')));
    }

    private function haciendaMatch(int $newValueEur): HaciendaMatch
    {
        return new HaciendaMatch($newValueEur, 'VOLKSWAGEN', 'GOLF 2.0 TDI', 2026, 1, HaciendaMatch::CONFIDENCE_HIGH);
    }

    private function calculatorWithCorrection(): SpainImportCalculator
    {
        $config = config('spain_import');
        $config['hacienda']['apply_foreign_registration_correction'] = true;

        return new SpainImportCalculator($config);
    }

    private function makeCalculator(): SpainImportCalculator
    {
        return new SpainImportCalculator(config('spain_import'));
    }

    private function input(
        ?int $co2 = 130,
        ?int $catalogusprijs = 30000,
        ?CarbonImmutable $datum = null,
        ?string $voertuigsoort = 'Personenauto',
        bool $residencyChange = false,
        string $autonomia = 'default',
        ?HaciendaMatch $hacienda = null,
    ): SpainImportInput {
        return new SpainImportInput(
            datumEersteToelating: $datum ?? CarbonImmutable::create(2020, 1, 1),
            co2: $co2,
            catalogusprijsEur: $catalogusprijs,
            voertuigsoort: $voertuigsoort,
            residencyChange: $residencyChange,
            autonomia: $autonomia,
            hacienda: $hacienda,
        );
    }
}
