<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class LookupControllerTest extends TestCase
{
    public function test_it_returns_vehicle_bpm_and_import_costs_for_a_known_kenteken(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'voertuigsoort' => 'Personenauto',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'datum_eerste_toelating' => '20190401',
                'massa_ledig_voertuig' => '1280',
                'catalogusprijs' => '31500',
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
            ->assertJsonPath('bpm.is_eligible', true)
            ->assertJsonPath('bpm.method', 'forfaitair')
            ->assertJsonPath('import_costs.iedmt_exempt', false)
            ->assertJsonPath('import_costs.autonomia', 'default')
            ->assertJsonStructure([
                'bpm' => [
                    'is_eligible', 'ineligible_reason', 'bruto_bpm_eur', 'rest_bpm_eur',
                    'afschrijving_pct', 'age_months', 'method', 'notes',
                ],
                'import_costs' => [
                    'iedmt_eur', 'iedmt_rate_pct', 'iedmt_exempt', 'iedmt_exempt_reason',
                    'estimated_market_value_eur', 'fixed_costs', 'fixed_costs_total_eur',
                    'total_eur', 'autonomia', 'notes',
                ],
                'net_effect_eur',
            ]);
    }

    public function test_it_honors_residency_change_flag_to_exempt_iedmt(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'merk' => 'VOLKSWAGEN',
                'datum_eerste_toelating' => '20190401',
                'catalogusprijs' => '31500',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '180',
            ]]),
        ]);

        $response = $this->postJson(route('api.lookup'), [
            'kenteken' => '12-abc-3',
            'residency_change' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('import_costs.iedmt_exempt', true)
            ->assertJsonPath('import_costs.iedmt_eur', 0);
    }

    public function test_commercial_vehicle_lookup_includes_classification_note(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'voertuigsoort' => 'Bedrijfsauto',
                'merk' => 'CITROEN',
                'handelsbenaming' => 'NEMO',
                'datum_eerste_toelating' => '20140101',
                'catalogusprijs' => '15000',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Diesel',
                'co2_uitstoot_gecombineerd' => '110',
            ]]),
        ]);

        $response = $this->postJson(route('api.lookup'), ['kenteken' => '12-abc-3']);

        $response->assertOk();
        $notes = $response->json('import_costs.notes');
        $this->assertNotEmpty($notes);
        $this->assertStringContainsString('bedrijfsauto', strtolower($notes[0]));
    }

    public function test_it_marks_pre_2006_cars_as_ineligible(): void
    {
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => 'OLD123',
                'merk' => 'VOLVO',
                'datum_eerste_toelating' => '20060101',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([[
                'kenteken' => 'OLD123',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '180',
            ]]),
        ]);

        $response = $this->postJson(route('api.lookup'), ['kenteken' => 'OLD-12-3']);

        $response->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('bpm.is_eligible', false)
            ->assertJsonPath('bpm.rest_bpm_eur', 0);
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
