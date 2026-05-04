<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Rdw;

use App\Services\Rdw\RdwService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class RdwServiceTest extends TestCase
{
    public function test_it_fetches_vehicle_data_from_rdw(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'voertuigsoort' => 'Personenauto',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'datum_eerste_toelating' => '20190401',
                'massa_ledig_voertuig' => '1280',
                'wam_verzekerd' => 'Ja',
            ]]),
        ]);

        $service = $this->makeService();

        $vehicle = $service->lookupVehicle('12-abc-3');

        $this->assertNotNull($vehicle);
        $this->assertSame('VOLKSWAGEN', $vehicle->merk);
        $this->assertSame('GOLF', $vehicle->handelsbenaming);
        $this->assertSame(1280, $vehicle->massaLedigVoertuig);
        $this->assertTrue($vehicle->wamVerzekerd);
        $this->assertSame('2019-04-01', $vehicle->datumEersteToelating?->toDateString());
    }

    public function test_it_returns_null_when_rdw_returns_empty_array(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([]),
        ]);

        $service = $this->makeService();

        $this->assertNull($service->lookupVehicle('00ZZZ0'));
    }

    public function test_it_caches_results_per_kenteken(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'merk' => 'VOLKSWAGEN',
            ]]),
        ]);

        $service = $this->makeService();

        $service->lookupVehicle('12ABC3');
        $service->lookupVehicle('12ABC3');

        Http::assertSentCount(1);
    }

    public function test_it_caches_not_found_results_too(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([]),
        ]);

        $service = $this->makeService();

        $this->assertNull($service->lookupVehicle('00ZZZ0'));
        $this->assertNull($service->lookupVehicle('00ZZZ0'));

        Http::assertSentCount(1);
    }

    public function test_it_sends_app_token_header_when_configured(): void
    {
        Http::fake([
            'opendata.rdw.nl/*' => Http::response([['kenteken' => '12ABC3', 'merk' => 'VW']]),
        ]);

        $service = $this->makeService(appToken: 'test-token');

        $service->lookupVehicle('12ABC3');

        Http::assertSent(fn ($request) => $request->hasHeader('X-App-Token', 'test-token'));
    }

    public function test_full_lookup_returns_not_found_when_vehicle_missing_and_skips_fuel_call(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([['kenteken' => 'X']]),
        ]);

        $service = $this->makeService();

        $result = $service->fullLookup('00ZZZ0');

        $this->assertFalse($result->found());
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '8ys7-d773'));
    }

    public function test_full_lookup_combines_vehicle_and_fuel_data(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'merk' => 'VOLKSWAGEN',
                'datum_eerste_toelating' => '20190401',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '112',
            ]]),
        ]);

        $service = $this->makeService();

        $result = $service->fullLookup('12ABC3');

        $this->assertTrue($result->found());
        $this->assertSame('VOLKSWAGEN', $result->vehicle?->merk);
        $this->assertSame('Benzine', $result->fuel?->brandstofOmschrijving);
        $this->assertSame(112, $result->fuel?->co2UitstootGecombineerd);
    }

    private function makeService(?string $appToken = null): RdwService
    {
        return new RdwService(
            http: app(\Illuminate\Http\Client\Factory::class),
            cache: Cache::store('array'),
            logger: app('log')->channel('null'),
            vehicleEndpoint: 'https://opendata.rdw.nl/resource/m9d7-ebf2.json',
            fuelEndpoint: 'https://opendata.rdw.nl/resource/8ys7-d773.json',
            appToken: $appToken,
            cacheTtlDays: 7,
            timeoutSeconds: 5,
        );
    }
}
