<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Bpm;

use App\Services\Bpm\BpmCalculator;
use App\Services\Rdw\Dto\FuelData;
use App\Services\Rdw\Dto\VehicleData;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class BpmCalculatorTest extends TestCase
{
    public function test_it_returns_null_when_lookup_failed(): void
    {
        $calculator = $this->makeCalculator();

        $this->assertNull($calculator->calculate(VehicleLookupResult::notFound('00ZZZ0')));
    }

    public function test_it_calculates_refund_for_a_recent_petrol_car(): void
    {
        $calculator = $this->makeCalculator();
        $now = CarbonImmutable::create(2025, 1, 1);

        $result = new VehicleLookupResult(
            kenteken: '12ABC3',
            vehicle: $this->vehicle(datumEersteToelating: CarbonImmutable::create(2024, 1, 1)),
            fuel: $this->fuel(brandstof: 'Benzine', co2: 100),
        );

        $indication = $calculator->calculate($result, $now);

        $this->assertNotNull($indication);
        // Original BPM = 440 + (100-79) * 162 = 3842; depreciation at ~12 months = 24%
        // Refund = 3842 * 0.76 = 2920 (rounded)
        $this->assertSame(3842, $indication->originalBpmEur);
        $this->assertSame(2920, $indication->estimatedRefundEur);
        $this->assertSame(0.24, $indication->depreciationFactor);
    }

    public function test_diesel_gets_surcharge_added_to_original_bpm(): void
    {
        $calculator = $this->makeCalculator();
        $now = CarbonImmutable::create(2025, 1, 1);

        $petrol = new VehicleLookupResult('A', $this->vehicle(CarbonImmutable::create(2024, 1, 1)), $this->fuel('Benzine', 100));
        $diesel = new VehicleLookupResult('B', $this->vehicle(CarbonImmutable::create(2024, 1, 1)), $this->fuel('Diesel', 100));

        $petrolBpm = $calculator->calculate($petrol, $now);
        $dieselBpm = $calculator->calculate($diesel, $now);

        $this->assertNotNull($petrolBpm);
        $this->assertNotNull($dieselBpm);
        $this->assertGreaterThan($petrolBpm->originalBpmEur, $dieselBpm->originalBpmEur);
    }

    public function test_old_car_has_high_depreciation_and_low_refund(): void
    {
        $calculator = $this->makeCalculator();
        $now = CarbonImmutable::create(2025, 1, 1);

        $result = new VehicleLookupResult(
            kenteken: 'OLD',
            vehicle: $this->vehicle(CarbonImmutable::create(2010, 1, 1)),  // 15 yr old
            fuel: $this->fuel('Benzine', 100),
        );

        $indication = $calculator->calculate($result, $now);

        $this->assertNotNull($indication);
        $this->assertGreaterThanOrEqual(0.86, $indication->depreciationFactor);
        $this->assertLessThan($indication->originalBpmEur * 0.15, $indication->estimatedRefundEur);
    }

    public function test_diesel_minimum_residual_floor_applies(): void
    {
        $calculator = $this->makeCalculator();
        $now = CarbonImmutable::create(2025, 1, 1);

        $result = new VehicleLookupResult(
            kenteken: 'OLD',
            vehicle: $this->vehicle(CarbonImmutable::create(2000, 1, 1)),  // 25 yr old
            fuel: $this->fuel('Diesel', 100),
        );

        $indication = $calculator->calculate($result, $now);

        $this->assertNotNull($indication);
        // Even at max depreciation (92%), diesel keeps minimum 12% residual
        $minRefund = (int) floor($indication->originalBpmEur * 0.12);
        $this->assertGreaterThanOrEqual($minRefund - 1, $indication->estimatedRefundEur);
    }

    private function makeCalculator(): BpmCalculator
    {
        return new BpmCalculator(config('bpm'));
    }

    private function vehicle(CarbonImmutable $datumEersteToelating): VehicleData
    {
        return new VehicleData(
            kenteken: '12ABC3',
            voertuigsoort: 'Personenauto',
            merk: 'TEST',
            handelsbenaming: 'TEST',
            inrichting: null,
            eersteKleur: null,
            aantalZitplaatsen: null,
            datumEersteToelating: $datumEersteToelating,
            datumEersteTenaamstellingNl: null,
            vervaldatumApk: null,
            massaLedigVoertuig: 1280,
            cilinderinhoud: null,
            catalogusprijs: null,
            wamVerzekerd: null,
        );
    }

    private function fuel(string $brandstof, int $co2): FuelData
    {
        return new FuelData(
            kenteken: '12ABC3',
            brandstofOmschrijving: $brandstof,
            co2UitstootGecombineerd: $co2,
            co2UitstootGewogen: null,
            emissiecodeOmschrijving: null,
        );
    }
}
