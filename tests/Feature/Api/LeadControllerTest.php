<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Filament\Resources\Leads\LeadResource;
use App\Mail\LeadReceivedNotification;
use App\Mail\QuoteRequestConfirmation;
use App\Models\Lead;
use App\Services\Leads\LeadConverter;
use App\Services\Leads\LeadTelegramAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_full_quote_snapshots_when_kenteken_is_known(): void
    {
        Mail::fake();
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'voertuigsoort' => 'Personenauto',
                'datum_eerste_toelating' => '20190401',
                'massa_ledig_voertuig' => '1280',
                'catalogusprijs' => '31500',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '130',
            ]]),
        ]);

        $this->postJson(route('api.leads.store'), $this->validPayload(['kenteken' => '12-ABC-3']))
            ->assertCreated();

        $lead = Lead::firstOrFail();

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
        Http::fake([
            'opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'merk' => 'VOLKSWAGEN',
                'handelsbenaming' => 'GOLF',
                'voertuigsoort' => 'Personenauto',
                'datum_eerste_toelating' => '20190401',
                'catalogusprijs' => '31500',
            ]]),
            'opendata.rdw.nl/resource/8ys7-d773.json*' => Http::response([[
                'kenteken' => '12ABC3',
                'brandstof_omschrijving' => 'Benzine',
                'co2_uitstoot_gecombineerd' => '130',
            ]]),
        ]);

        $this->postJson(route('api.leads.store'), $this->validPayload(['kenteken' => '12-ABC-3']))->assertCreated();
        $lead = Lead::firstOrFail();

        $dossier = app(LeadConverter::class)->convert($lead, 895);

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

    public function test_it_stores_the_lead_without_snapshots_when_the_rdw_is_down(): void
    {
        Mail::fake();
        Http::fake([
            'opendata.rdw.nl/*' => Http::response('Service Unavailable', 503),
        ]);

        $this->postJson(route('api.leads.store'), $this->validPayload())->assertCreated();

        $lead = Lead::firstOrFail();
        $this->assertNull($lead->rdw_snapshot_json);
        $this->assertNull($lead->bpm_calculation_json);
        Mail::assertSent(LeadReceivedNotification::class);
        Mail::assertSent(QuoteRequestConfirmation::class);
    }

    public function test_conversion_backfills_snapshots_for_a_lead_stored_during_an_rdw_outage(): void
    {
        Mail::fake();
        $rdwIsUp = false;
        Http::fake(function (Request $request) use (&$rdwIsUp) {
            if (! $rdwIsUp) {
                return Http::response('Service Unavailable', 503);
            }

            return str_contains($request->url(), 'm9d7-ebf2')
                ? Http::response([[
                    'kenteken' => '12ABC3',
                    'merk' => 'VOLKSWAGEN',
                    'handelsbenaming' => 'GOLF',
                    'voertuigsoort' => 'Personenauto',
                    'datum_eerste_toelating' => '20190401',
                    'catalogusprijs' => '31500',
                ]])
                : Http::response([[
                    'kenteken' => '12ABC3',
                    'brandstof_omschrijving' => 'Benzine',
                    'co2_uitstoot_gecombineerd' => '130',
                ]]);
        });

        $this->postJson(route('api.leads.store'), $this->validPayload(['kenteken' => '12-ABC-3']))->assertCreated();
        $lead = Lead::firstOrFail();
        $this->assertNull($lead->rdw_snapshot_json);

        $rdwIsUp = true;
        $dossier = app(LeadConverter::class)->convert($lead, 895);

        $this->assertSame('VOLKSWAGEN', $dossier->merk);
        $this->assertIsArray($dossier->bpm_calculation_json);
        $this->assertIsArray($lead->fresh()->rdw_snapshot_json);
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

    public function test_it_sends_a_telegram_alert_with_a_prefilled_whatsapp_link(): void
    {
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_id' => '12345',
            'services.telegram.lead_forward_whatsapp' => '+34 600 11 22 33',
        ]);

        $this->postJson(route('api.leads.store'), $this->validPayload(['name' => 'Jan & Co']))
            ->assertCreated();

        $lead = Lead::firstOrFail();

        Http::assertSent(function (Request $request) use ($lead): bool {
            if ($request->url() !== 'https://api.telegram.org/bottest-token/sendMessage') {
                return false;
            }

            $whatsappUrl = $request['reply_markup']['inline_keyboard'][0][0]['url'];
            parse_str((string) parse_url($whatsappUrl, PHP_URL_QUERY), $query);

            return $request['chat_id'] === '12345'
                && str_contains($request['text'], sprintf('Nieuwe lead #%05d', $lead->id))
                && str_contains($request['text'], 'Jan &amp; Co')
                && str_starts_with($whatsappUrl, 'https://wa.me/34600112233?text=')
                && $query['text'] === "Nieuwe klant:\nNaam: Jan & Co\nLocatie: Marbella, Costa del Sol\nTelefoon: +31612345678\nLead: ".LeadResource::getUrl('view', ['record' => $lead]);
        });
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function phoneNumbers(): array
    {
        return [
            'international with spaces' => ['+31 6 12345678', '+31612345678'],
            'international compact' => ['+34650015942', '+34650015942'],
            'double zero prefix' => ['0031 6 1234 5678', '+31612345678'],
            'dutch mobile' => ['06-12345678', '+31612345678'],
            'spanish without country code' => ['650 015 942', '+34650015942'],
            'unrecognised stays as entered' => ['12345', '12345'],
        ];
    }

    #[DataProvider('phoneNumbers')]
    public function test_whatsapp_text_writes_phone_numbers_in_international_format(string $entered, string $expected): void
    {
        $this->assertSame($expected, app(LeadTelegramAlert::class)->internationalPhone($entered));
    }

    public function test_it_skips_the_telegram_alert_when_it_is_not_configured(): void
    {
        Mail::fake();
        Http::fake();
        config(['services.telegram.bot_token' => null, 'services.telegram.chat_id' => null]);

        $this->postJson(route('api.leads.store'), $this->validPayload())->assertCreated();

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.telegram.org'));
    }

    public function test_a_failing_telegram_alert_does_not_block_the_lead_or_the_mails(): void
    {
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false], 500)]);
        config(['services.telegram.bot_token' => 'test-token', 'services.telegram.chat_id' => '12345']);

        $this->postJson(route('api.leads.store'), $this->validPayload())->assertCreated();

        $this->assertDatabaseCount('leads', 1);
        Mail::assertSent(LeadReceivedNotification::class);
        Mail::assertSent(QuoteRequestConfirmation::class);
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
