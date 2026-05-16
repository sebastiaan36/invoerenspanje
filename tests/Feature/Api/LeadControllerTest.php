<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Mail\LeadReceivedNotification;
use App\Mail\QuoteRequestConfirmation;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_full_quote_snapshots_when_kenteken_is_known(): void
    {
        Mail::fake();
        \Illuminate\Support\Facades\Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => \Illuminate\Support\Facades\Http::response([[
                'kenteken' => '12ABC3',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'voertuigsoort' => 'Personenauto',
                'datum_eerste_toelating' => '20190401',
                'massa_ledig_voertuig' => '1280',
                'catalogusprijs' => '31500',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => \Illuminate\Support\Facades\Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '130',
            ]]),
        ]);

        $this->postJson(route('api.leads.store'), $this->validPayload(['kenteken' => '12-ABC-3']))
            ->assertCreated();

        $lead = \App\Models\Lead::firstOrFail();

        $this->assertIsArray($lead->rdw_snapshot_json);
        $this->assertSame('VOLKSWAGEN', $lead->rdw_snapshot_json['vehicle']['merk']);
        $this->assertSame('GOLF', $lead->rdw_snapshot_json['vehicle']['handelsbenaming']);
        $this->assertSame(31500, $lead->rdw_snapshot_json['vehicle']['catalogusprijs']);
        $this->assertSame('Benzine', $lead->rdw_snapshot_json['fuel']['brandstof']);
        $this->assertSame(130, $lead->rdw_snapshot_json['fuel']['co2_gecombineerd']);

        $this->assertIsArray($lead->bpm_calculation_json);
        $this->assertTrue($lead->bpm_calculation_json['is_eligible']);
        $this->assertGreaterThan(0, $lead->bpm_calculation_json['bruto_bpm_eur']);
        $this->assertSame('forfaitair', $lead->bpm_calculation_json['method']);

        $this->assertIsArray($lead->import_calculation_json);
        $this->assertSame(4.75, $lead->import_calculation_json['iedmt_rate_pct']);
        $this->assertGreaterThan(0, $lead->import_calculation_json['fixed_costs_total_eur']);
        $this->assertNotEmpty($lead->import_calculation_json['fixed_costs']);
    }

    public function test_lead_to_dossier_conversion_copies_snapshots_and_vehicle_details(): void
    {
        Mail::fake();
        \Illuminate\Support\Facades\Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => \Illuminate\Support\Facades\Http::response([[
                'kenteken' => '12ABC3',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'voertuigsoort' => 'Personenauto',
                'datum_eerste_toelating' => '20190401',
                'catalogusprijs' => '31500',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => \Illuminate\Support\Facades\Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '130',
            ]]),
        ]);

        $this->postJson(route('api.leads.store'), $this->validPayload(['kenteken' => '12-ABC-3']))->assertCreated();
        $lead = \App\Models\Lead::firstOrFail();

        $dossier = app(\App\Services\Leads\LeadConverter::class)->convert($lead, 895);

        $this->assertSame('VOLKSWAGEN', $dossier->merk);
        $this->assertSame('GOLF', $dossier->model);
        $this->assertSame('Benzine', $dossier->brandstof);
        $this->assertSame(130, $dossier->co2);
        $this->assertSame('2019-04-01', $dossier->datum_eerste_toelating?->toDateString());
        $this->assertIsArray($dossier->rdw_data_json);
        $this->assertIsArray($dossier->bpm_calculation_json);
        $this->assertIsArray($dossier->import_calculation_json);
        $this->assertSame($lead->bpm_calculation_json, $dossier->bpm_calculation_json);
        $this->assertSame($lead->import_calculation_json, $dossier->import_calculation_json);
    }

    public function test_it_stores_a_lead_and_sends_two_mails(): void
    {
        Mail::fake();

        $payload = $this->validPayload();

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['lead_id', 'reference']);

        $this->assertDatabaseCount('leads', 1);

        /** @var Lead $lead */
        $lead = Lead::firstOrFail();
        $this->assertSame('Sebastiaan Test', $lead->name);
        $this->assertSame('seb@example.com', $lead->email);
        $this->assertSame('12ABC3', $lead->kenteken);
        $this->assertSame('compleet', $lead->package_slug);
        $this->assertSame('Marbella, Costa del Sol', $lead->woonplaats_spanje);
        $this->assertTrue($lead->residency_change);
        $this->assertSame('costa_del_sol', $lead->autonomia);
        $this->assertSame(1500, $lead->bpm_teruggave_indicatie_eur);
        $this->assertSame(750, $lead->import_kosten_indicatie_eur);
        $this->assertSame(1645, $lead->totaalprijs_indicatie_eur);
        $this->assertSame('nieuw', $lead->status);
        $this->assertSame('organic', $lead->source); // geen utm_source

        Mail::assertSent(LeadReceivedNotification::class, function ($mail) {
            return $mail->hasTo('info@autoinvoerenspanje.nl');
        });

        Mail::assertSent(QuoteRequestConfirmation::class, function ($mail) use ($lead) {
            return $mail->hasTo($lead->email);
        });
    }

    public function test_it_marks_source_as_ads_when_utm_source_is_present(): void
    {
        Mail::fake();

        $payload = $this->validPayload([
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'spaans-kenteken',
        ]);

        $this->postJson(route('api.leads.store'), $payload)->assertCreated();

        /** @var Lead $lead */
        $lead = Lead::firstOrFail();
        $this->assertSame('ads', $lead->source);
        $this->assertSame('google', $lead->utm_source);
        $this->assertSame('cpc', $lead->utm_medium);
    }

    public function test_it_validates_required_fields(): void
    {
        Mail::fake();

        $response = $this->postJson(route('api.leads.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name', 'email', 'phone', 'regio', 'kenteken', 'package_slug',
            ]);

        Mail::assertNothingSent();
    }

    public function test_it_rejects_unknown_package_slug(): void
    {
        Mail::fake();

        $payload = $this->validPayload(['package_slug' => 'platinum_unicorn']);

        $this->postJson(route('api.leads.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['package_slug']);

        Mail::assertNothingSent();
    }

    public function test_it_rejects_invalid_kenteken_format(): void
    {
        Mail::fake();

        $payload = $this->validPayload(['kenteken' => '???']);

        $this->postJson(route('api.leads.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['kenteken']);
    }

    public function test_it_rejects_invalid_email(): void
    {
        Mail::fake();

        $payload = $this->validPayload(['email' => 'not-an-email']);

        $this->postJson(route('api.leads.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_a_filled_honeypot(): void
    {
        Mail::fake();

        $payload = $this->validPayload(['website' => 'http://spam.example']);

        $this->postJson(route('api.leads.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['website']);

        Mail::assertNothingSent();
    }

    public function test_it_rate_limits_after_five_submissions_per_minute(): void
    {
        Mail::fake();

        $payload = $this->validPayload();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('api.leads.store'), $payload)->assertCreated();
        }

        $this->postJson(route('api.leads.store'), $payload)->assertStatus(429);

        $this->assertSame(5, Lead::count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Sebastiaan Test',
            'email' => 'seb@example.com',
            'phone' => '+31 6 12345678',
            'regio' => 'Marbella, Costa del Sol',
            'expected_move_date' => 'juni 2026',
            'comment' => 'Heb nog een vraag over de transport-optie.',
            'kenteken' => '12-ABC-3',
            'package_slug' => 'compleet',
            'residency_change' => true,
            'autonomia' => 'costa_del_sol',
            'bpm_teruggave_indicatie' => 1500,
            'import_kosten_indicatie' => 750,
            'totaalprijs_indicatie' => 1645,
        ], $overrides);
    }
}
