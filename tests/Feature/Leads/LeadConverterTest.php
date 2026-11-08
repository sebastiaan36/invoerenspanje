<?php

declare(strict_types=1);

namespace Tests\Feature\Leads;

use App\Mail\WelcomeKlantMail;
use App\Models\Dossier;
use App\Models\Lead;
use App\Models\User;
use App\Services\Leads\LeadConversionRefusedException;
use App\Services\Leads\LeadConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

final class LeadConverterTest extends TestCase
{
    use RefreshDatabase;

    private const string NEW_PASSWORD = 'Sterk-Wachtwoord-2026!';

    protected function setUp(): void
    {
        parent::setUp();

        // Leads below carry snapshots, so the converter never needs the RDW.
        Http::preventStrayRequests();
    }

    public function test_it_creates_a_klant_and_a_dossier_and_sends_the_welcome_mail(): void
    {
        Mail::fake();
        $lead = $this->leadWithSnapshots();

        $dossier = $this->converter()->convert($lead, 895);

        $user = User::sole();
        $this->assertSame('klant', $user->role);
        $this->assertSame($user->id, $dossier->user_id);
        $this->assertSame($lead->id, $dossier->lead_id);
        $this->assertSame(895, $dossier->service_fee_eur);
        $this->assertSame('gewonnen', $lead->fresh()->status);

        Mail::assertSent(WelcomeKlantMail::class, fn (WelcomeKlantMail $mail): bool => $mail->hasTo($user->email)
            && $mail->dossier->is($dossier));
    }

    public function test_it_stores_the_wltp_co2_value_before_the_nedc_value(): void
    {
        Mail::fake();

        $dossier = $this->converter()->convert($this->leadWithSnapshots(fuel: [
            'brandstof' => 'Benzine',
            'co2_gecombineerd' => 130,
            'co2_wltp_gecombineerd' => 152,
        ]));

        $this->assertSame(152, $dossier->co2);
    }

    public function test_it_falls_back_to_the_nedc_co2_value_without_a_wltp_value(): void
    {
        Mail::fake();

        $dossier = $this->converter()->convert($this->leadWithSnapshots(fuel: [
            'brandstof' => 'Benzine',
            'co2_gecombineerd' => 130,
            'co2_wltp_gecombineerd' => null,
        ]));

        $this->assertSame(130, $dossier->co2);
    }

    public function test_the_bpm_indication_comes_from_the_server_calculation_not_the_lead_figure(): void
    {
        Mail::fake();
        $lead = $this->leadWithSnapshots(bpm: ['is_eligible' => true, 'rest_bpm_eur' => 2345.6]);
        $lead->update(['bpm_teruggave_indicatie_eur' => 999999]);

        $dossier = $this->converter()->convert($lead);

        $this->assertSame(2346, $dossier->bpm_indicatie_eur);
    }

    public function test_the_bpm_indication_is_zero_when_the_car_is_not_eligible(): void
    {
        Mail::fake();

        $dossier = $this->converter()->convert(
            $this->leadWithSnapshots(bpm: ['is_eligible' => false, 'rest_bpm_eur' => 0.0]),
        );

        $this->assertSame(0, $dossier->bpm_indicatie_eur);
    }

    public function test_it_matches_an_existing_user_case_insensitively(): void
    {
        Mail::fake();
        $existing = User::factory()->create(['email' => 'Jan.Klant@Example.com', 'role' => 'klant']);
        $lead = $this->leadWithSnapshots(['email' => 'jan.klant@example.COM']);

        $dossier = $this->converter()->convert($lead);

        $this->assertSame(1, User::count());
        $this->assertSame($existing->id, $dossier->user_id);
    }

    public function test_a_new_user_is_stored_with_a_lowercase_email(): void
    {
        Mail::fake();

        $this->converter()->convert($this->leadWithSnapshots(['email' => 'Nieuwe.Klant@Example.COM']));

        $this->assertSame('nieuwe.klant@example.com', User::sole()->email);
    }

    public function test_it_refuses_to_convert_a_lead_with_an_admin_email(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create(['email' => 'beheer@example.com']);
        $lead = $this->leadWithSnapshots(['email' => 'Beheer@Example.com']);

        try {
            $this->converter()->convert($lead);
            $this->fail('Expected the conversion to be refused.');
        } catch (LeadConversionRefusedException $e) {
            $this->assertStringContainsString('beheerder', $e->getMessage());
        }

        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertSame(0, Dossier::count());
        $this->assertNotSame('gewonnen', $lead->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_the_welcome_mail_is_not_sent_when_the_transaction_rolls_back(): void
    {
        Mail::fake();
        $lead = $this->leadWithSnapshots();

        try {
            DB::transaction(function () use ($lead): void {
                $this->converter()->convert($lead);

                throw new RuntimeException('Rollback na het aanmaken van het dossier.');
            });
        } catch (RuntimeException) {
            // Expected.
        }

        $this->assertSame(0, Dossier::count());
        Mail::assertNothingSent();
        $this->assertDatabaseCount('welcome_password_tokens', 0);
    }

    public function test_the_welcome_mail_link_still_works_after_three_days(): void
    {
        Mail::fake();
        $this->converter()->convert($this->leadWithSnapshots(['email' => 'klant@example.com']));

        $token = null;
        Mail::assertSent(WelcomeKlantMail::class, function (WelcomeKlantMail $mail) use (&$token): bool {
            $token = $mail->passwordResetToken;

            return true;
        });

        $this->travel(3)->days();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'klant@example.com',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, User::sole()->password));
    }

    private function converter(): LeadConverter
    {
        return app(LeadConverter::class);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $fuel
     * @param  array<string, mixed>  $bpm
     */
    private function leadWithSnapshots(array $attributes = [], array $fuel = [], array $bpm = []): Lead
    {
        return Lead::factory()->create([
            'kenteken' => '12ABC3',
            'autonomia' => 'costa_del_sol',
            'rdw_snapshot_json' => [
                'kenteken' => '12ABC3',
                'vehicle' => [
                    'merk' => 'VOLKSWAGEN',
                    'handelsbenaming' => 'GOLF',
                    'datum_eerste_toelating' => '2019-04-01',
                ],
                'fuel' => $fuel + [
                    'brandstof' => 'Benzine',
                    'co2_gecombineerd' => 130,
                    'co2_wltp_gecombineerd' => null,
                ],
            ],
            'bpm_calculation_json' => $bpm + [
                'is_eligible' => true,
                'ineligible_reason' => null,
                'bruto_bpm_eur' => 6000.0,
                'rest_bpm_eur' => 1800.0,
                'method' => 'forfaitair',
                'notes' => [],
            ],
            'import_calculation_json' => ['total_eur' => 750.0],
            ...$attributes,
        ]);
    }
}
