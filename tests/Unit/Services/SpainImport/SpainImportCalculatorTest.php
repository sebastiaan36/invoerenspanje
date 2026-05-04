<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SpainImport;

use App\Services\SpainImport\Dto\SpainImportInput;
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

        $this->assertNotEmpty($result->notes);
        $this->assertStringContainsString('bedrijfsauto', strtolower($result->notes[0]));
    }

    public function test_passenger_car_does_not_get_commercial_note(): void
    {
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 100, voertuigsoort: 'Personenauto');

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertEmpty($result->notes);
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
        $calculator = $this->makeCalculator();
        $input = $this->input(co2: 250, catalogusprijs: null);

        $result = $calculator->calculate($input, CarbonImmutable::create(2025, 1, 1));

        $this->assertSame(0.0, $result->estimatedMarketValueEur);
        $this->assertSame(0.0, $result->iedmtEur);
        // Fixed costs still apply.
        $this->assertGreaterThan(0.0, $result->fixedCostsTotalEur);
    }

    private function makeCalculator(): SpainImportCalculator
    {
        return new SpainImportCalculator(config('spain_import'));
    }

    private function input(
        int $co2 = 130,
        ?int $catalogusprijs = 30000,
        ?CarbonImmutable $datum = null,
        ?string $voertuigsoort = 'Personenauto',
        bool $residencyChange = false,
        string $autonomia = 'default',
    ): SpainImportInput {
        return new SpainImportInput(
            datumEersteToelating: $datum ?? CarbonImmutable::create(2020, 1, 1),
            co2: $co2,
            catalogusprijsEur: $catalogusprijs,
            voertuigsoort: $voertuigsoort,
            residencyChange: $residencyChange,
            autonomia: $autonomia,
        );
    }
}
