<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Bpm;

use App\Services\Bpm\Dto\BpmInput;
use App\Services\Rdw\Dto\FuelData;
use App\Services\Rdw\Dto\VehicleData;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Tests\TestCase;

final class BpmInputTest extends TestCase
{
    public function test_a_plugin_hybrid_uses_the_weighted_co2(): void
    {
        $result = $this->lookupResult(isPluginHybrid: true);

        $input = BpmInput::fromLookup($result);

        $this->assertNotNull($input);
        $this->assertTrue($input->isPlugin);
        $this->assertSame(40, $input->co2);
        $this->assertSame('Benzine', $input->brandstof);
    }

    public function test_a_normal_car_uses_the_combined_co2(): void
    {
        $result = $this->lookupResult(isPluginHybrid: false);

        $input = BpmInput::fromLookup($result);

        $this->assertNotNull($input);
        $this->assertFalse($input->isPlugin);
        $this->assertSame(130, $input->co2);
    }

    public function test_a_normal_car_prefers_the_wltp_combined_co2(): void
    {
        $vehicle = VehicleData::fromRdwRow(['datum_eerste_toelating' => '20230415']);
        // Modern cars often only have the WLTP field populated.
        $fuel = FuelData::fromRdwRow([
            'brandstof_omschrijving' => 'Benzine',
            'emissie_co2_gecombineerd_wltp' => '142',
        ]);
        $result = new VehicleLookupResult('12ABC3', $vehicle, $fuel);

        $input = BpmInput::fromLookup($result);

        $this->assertNotNull($input);
        $this->assertSame(142, $input->co2);
    }

    public function test_it_carries_the_voertuigsoort_through(): void
    {
        $result = $this->lookupResult(isPluginHybrid: false, voertuigsoort: 'Bedrijfsauto');

        $input = BpmInput::fromLookup($result);

        $this->assertNotNull($input);
        $this->assertSame('Bedrijfsauto', $input->voertuigsoort);
    }

    public function test_it_carries_the_rdw_historische_bruto_bpm_through(): void
    {
        $vehicle = VehicleData::fromRdwRow([
            'datum_eerste_toelating' => '20070404',
            'voertuigsoort' => 'Personenauto',
            'bruto_bpm' => '3562',
        ]);
        $fuel = FuelData::fromRdwRow(['brandstof_omschrijving' => 'Benzine', 'co2_uitstoot_gecombineerd' => '155']);
        $result = new VehicleLookupResult('11XDDF', $vehicle, $fuel);

        $input = BpmInput::fromLookup($result);

        $this->assertNotNull($input);
        $this->assertSame(3562, $input->historischBrutoBpm);
    }

    public function test_a_combustion_car_without_co2_or_bruto_bpm_cannot_be_calculated(): void
    {
        $vehicle = VehicleData::fromRdwRow(['datum_eerste_toelating' => '20190415', 'voertuigsoort' => 'Personenauto']);
        $fuel = FuelData::fromRdwRow(['brandstof_omschrijving' => 'Benzine']);

        $this->assertNull(BpmInput::fromLookup(new VehicleLookupResult('12ABC3', $vehicle, $fuel)));
    }

    public function test_a_car_without_co2_but_with_rdw_bruto_bpm_can_be_calculated(): void
    {
        $vehicle = VehicleData::fromRdwRow(['datum_eerste_toelating' => '20190415', 'bruto_bpm' => '4100']);
        $fuel = FuelData::fromRdwRow(['brandstof_omschrijving' => 'Diesel']);

        $input = BpmInput::fromLookup(new VehicleLookupResult('12ABC3', $vehicle, $fuel));

        $this->assertNotNull($input);
        $this->assertSame(4100, $input->historischBrutoBpm);
    }

    public function test_an_electric_car_without_co2_can_be_calculated(): void
    {
        $vehicle = VehicleData::fromRdwRow(['datum_eerste_toelating' => '20220415']);
        $fuel = FuelData::fromRdwRow(['brandstof_omschrijving' => 'Elektriciteit']);

        $input = BpmInput::fromLookup(new VehicleLookupResult('12ABC3', $vehicle, $fuel));

        $this->assertNotNull($input);
        $this->assertSame(0, $input->co2);
    }

    private function lookupResult(bool $isPluginHybrid, string $voertuigsoort = 'Personenauto'): VehicleLookupResult
    {
        $vehicle = VehicleData::fromRdwRow([
            'datum_eerste_toelating' => '20190415',
            'voertuigsoort' => $voertuigsoort,
        ]);
        $fuel = FuelData::fromRdwRow([
            'brandstof_omschrijving' => 'Benzine',
            'co2_uitstoot_gecombineerd' => '130',
            'co2_uitstoot_gewogen' => '40',
        ]);

        return new VehicleLookupResult('12ABC3', $vehicle, $fuel, isPluginHybrid: $isPluginHybrid);
    }
}
