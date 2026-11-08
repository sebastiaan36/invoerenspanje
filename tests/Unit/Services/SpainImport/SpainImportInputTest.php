<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SpainImport;

use App\Services\Rdw\Dto\FuelData;
use App\Services\Rdw\Dto\VehicleData;
use App\Services\Rdw\Dto\VehicleLookupResult;
use App\Services\SpainImport\Dto\SpainImportInput;
use App\Services\SpainImport\SpainImportCalculator;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class SpainImportInputTest extends TestCase
{
    public function test_a_plugin_hybrid_uses_the_weighted_co2_and_lands_in_the_zero_bracket(): void
    {
        $input = SpainImportInput::fromLookup($this->lookup(isPluginHybrid: true));

        $this->assertNotNull($input);
        $this->assertSame(40, $input->co2);

        $result = (new SpainImportCalculator(config('spain_import')))->calculate($input, CarbonImmutable::create(2026, 1, 1));
        $this->assertSame(0.0, $result->iedmtRatePct);
        $this->assertSame(0.0, $result->iedmtEur);
    }

    public function test_a_normal_car_uses_the_combined_co2(): void
    {
        $input = SpainImportInput::fromLookup($this->lookup(isPluginHybrid: false));

        $this->assertSame(130, $input?->co2);
    }

    public function test_a_plugin_hybrid_prefers_the_wltp_weighted_value(): void
    {
        $fuel = FuelData::fromRdwRow([
            'brandstof_omschrijving' => 'Benzine',
            'emissie_co2_gecombineerd_wltp' => '150',
            'emissie_co2_gewogen_gecombineerd_wltp' => '25',
            'co2_uitstoot_gewogen' => '45',
        ]);

        $input = SpainImportInput::fromLookup(new VehicleLookupResult('12ABC3', $this->vehicle(), $fuel, isPluginHybrid: true));

        $this->assertSame(25, $input?->co2);
    }

    public function test_missing_fuel_rows_are_flagged(): void
    {
        $input = SpainImportInput::fromLookup(new VehicleLookupResult('12ABC3', $this->vehicle(), null));

        $this->assertNotNull($input);
        $this->assertTrue($input->fuelDataMissing);
        $this->assertNull($input->co2);
    }

    public function test_a_fuel_row_without_fuel_type_and_co2_counts_as_missing(): void
    {
        $fuel = FuelData::fromRdwRow(['kenteken' => '12ABC3']);

        $input = SpainImportInput::fromLookup(new VehicleLookupResult('12ABC3', $this->vehicle(), $fuel));

        $this->assertTrue($input?->fuelDataMissing);
    }

    public function test_a_fuel_row_with_a_fuel_type_but_no_co2_is_not_missing(): void
    {
        $fuel = FuelData::fromRdwRow(['brandstof_omschrijving' => 'Benzine']);

        $input = SpainImportInput::fromLookup(new VehicleLookupResult('12ABC3', $this->vehicle(), $fuel));

        $this->assertFalse($input?->fuelDataMissing);
    }

    public function test_the_flags_survive_a_residency_change_copy(): void
    {
        $input = SpainImportInput::fromLookup(new VehicleLookupResult('12ABC3', $this->vehicle(), null), haciendaTableAvailable: false);

        $copy = $input?->withResidencyChange(true);

        $this->assertTrue($copy?->fuelDataMissing);
        $this->assertFalse($copy?->haciendaTableAvailable);
        $this->assertTrue($copy?->residencyChange);
    }

    private function vehicle(): VehicleData
    {
        return VehicleData::fromRdwRow([
            'datum_eerste_toelating' => '20190415',
            'voertuigsoort' => 'Personenauto',
            'catalogusprijs' => '45000',
        ]);
    }

    private function lookup(bool $isPluginHybrid): VehicleLookupResult
    {
        $fuel = FuelData::fromRdwRow([
            'brandstof_omschrijving' => 'Benzine',
            'co2_uitstoot_gecombineerd' => '130',
            'co2_uitstoot_gewogen' => '40',
        ]);

        return new VehicleLookupResult('12ABC3', $this->vehicle(), $fuel, isPluginHybrid: $isPluginHybrid);
    }
}
