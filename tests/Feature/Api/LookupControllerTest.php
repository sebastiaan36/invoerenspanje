<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class LookupControllerTest extends TestCase
{
    public function test_it_returns_vehicle_and_bpm_for_a_known_kenteken(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'voertuigsoort' => 'Personenauto',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'datum_eerste_toelating' => '20190401',
                'massa_ledig_voertuig' => '1280',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '112',
            ]]),
        ]);

        $response = $this->postJson(route('api.lookup'), ['kenteken' => '12-abc-3']);

        $response->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('kenteken', '12ABC3')
            ->assertJsonPath('vehicle.merk', 'VOLKSWAGEN')
            ->assertJsonPath('fuel.brandstof', 'Benzine')
            ->assertJsonPath('fuel.co2_gecombineerd', 112)
            ->assertJsonStructure([
                'bpm' => ['estimated_refund_eur', 'original_bpm_eur', 'depreciation_factor', 'inputs', 'notes'],
            ]);
    }

    public function test_it_returns_404_when_kenteken_is_not_in_rdw(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([]),
        ]);

        $response = $this->postJson(route('api.lookup'), ['kenteken' => '00-zzz-0']);

        $response->assertNotFound()
            ->assertJsonPath('found', false);
    }

    public function test_it_validates_the_kenteken_format(): void
    {
        $response = $this->postJson(route('api.lookup'), ['kenteken' => '!!!']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['kenteken']);
    }

    public function test_it_requires_a_kenteken(): void
    {
        $response = $this->postJson(route('api.lookup'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['kenteken']);
    }

    public function test_it_returns_500_safe_response_when_rdw_is_down(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response('boom', 503),
        ]);

        $response = $this->postJson(route('api.lookup'), ['kenteken' => '12ABC3']);

        // Failed RDW call → service returns null → controller returns 404 with friendly message
        $response->assertNotFound()
            ->assertJsonPath('found', false);
    }
}
