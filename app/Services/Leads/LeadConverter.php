<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Http\Controllers\Auth\NewPasswordController;
use App\Mail\WelcomeKlantMail;
use App\Models\Dossier;
use App\Models\Lead;
use App\Models\User;
use App\Services\Quote\VehicleQuoteService;
use App\Services\Rdw\RdwUnavailableException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

final class LeadConverter
{
    public function __construct(
        private readonly VehicleQuoteService $quoteService,
    ) {}

    /**
     * Maak op basis van een lead een (eventueel bestaande) user + dossier aan en
     * stuur na de commit een welkomstmail met set-password link (7 dagen geldig).
     *
     * @throws LeadConversionRefusedException wanneer het e-mailadres bij een beheerder hoort
     */
    public function convert(Lead $lead, ?int $serviceFeeEur = null): Dossier
    {
        $email = mb_strtolower(trim($lead->email));

        if ($this->findUserByEmail($email)?->isAdmin()) {
            throw LeadConversionRefusedException::emailBelongsToAdmin($lead);
        }

        $this->backfillQuoteSnapshots($lead);

        return DB::transaction(function () use ($lead, $email, $serviceFeeEur): Dossier {
            $user = $this->findUserByEmail($email) ?? User::create([
                'email' => $email,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'role' => 'klant',
                // Sterk random wachtwoord — klant zet zelf via reset-link.
                'password' => bcrypt(Str::random(48)),
                'email_verified_at' => now(),
            ]);

            if ($user->isAdmin()) {
                throw LeadConversionRefusedException::emailBelongsToAdmin($lead);
            }

            // Vul ontbrekende velden bij bestaande user.
            $userDirty = false;
            if ($user->phone === null && $lead->phone) {
                $user->phone = $lead->phone;
                $userDirty = true;
            }
            if ($user->role !== 'klant') {
                $user->role = 'klant';
                $userDirty = true;
            }
            if ($userDirty) {
                $user->save();
            }

            // Voertuig-detail uit RDW snapshot trekken naar gestructureerde dossier-kolommen.
            $rdw = $lead->rdw_snapshot_json;
            $vehicle = $rdw['vehicle'] ?? null;
            $fuel = $rdw['fuel'] ?? null;

            $dossier = Dossier::create([
                'user_id' => $user->id,
                'lead_id' => $lead->id,
                'status' => Dossier::STATUS_AKKOORD,
                'kenteken' => $lead->kenteken,
                'merk' => $vehicle['merk'] ?? null,
                'model' => $vehicle['handelsbenaming'] ?? null,
                'datum_eerste_toelating' => $vehicle['datum_eerste_toelating'] ?? null,
                'brandstof' => $fuel['brandstof'] ?? null,
                // WLTP eerst; oudere auto's hebben alleen de NEDC-waarde.
                'co2' => $fuel['co2_wltp_gecombineerd'] ?? $fuel['co2_gecombineerd'] ?? null,
                'rdw_data_json' => $rdw,
                'pakket' => $lead->package_slug,
                'bpm_indicatie_eur' => $this->bpmIndicationEur($lead->bpm_calculation_json),
                'bpm_calculation_json' => $lead->bpm_calculation_json,
                'import_calculation_json' => $lead->import_calculation_json,
                'service_fee_eur' => $serviceFeeEur,
                'started_at' => now(),
            ]);

            // Lead afsluiten als gewonnen.
            $lead->update(['status' => 'gewonnen']);

            // Pas na de commit: geen welkomstmail (en geen token) voor een dossier
            // dat door een rollback nooit bestaan heeft.
            DB::afterCommit(fn () => $this->sendWelcomeMail($user, $dossier));

            return $dossier;
        });
    }

    private function findUserByEmail(string $email): ?User
    {
        return User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
    }

    /**
     * Server-side berekende teruggave uit de BPM-snapshot (niet het bedrag dat
     * de browser ooit meestuurde).
     *
     * @param  array<string, mixed>|null  $bpmCalculation
     */
    private function bpmIndicationEur(?array $bpmCalculation): ?int
    {
        if ($bpmCalculation === null) {
            return null;
        }

        if (($bpmCalculation['is_eligible'] ?? false) !== true) {
            return 0;
        }

        return (int) round((float) ($bpmCalculation['rest_bpm_eur'] ?? 0));
    }

    /**
     * Set-password link via de "welcome"-broker (7 dagen geldig). Een mislukte
     * mail mag het al opgeslagen dossier niet raken; we melden hem wel.
     */
    private function sendWelcomeMail(User $user, Dossier $dossier): void
    {
        try {
            $token = Password::broker(NewPasswordController::WELCOME_BROKER)->createToken($user);

            Mail::to($user->email)->send(new WelcomeKlantMail($user, $dossier, $token));
        } catch (Throwable $e) {
            report($e);
            Log::error('Welkomstmail voor nieuw dossier kon niet worden verstuurd.', [
                'dossier_id' => $dossier->id,
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Een lead die tijdens een RDW-storing binnenkwam heeft geen snapshots.
     * Probeer ze alsnog op te halen, zodat het dossier voertuiggegevens krijgt.
     */
    private function backfillQuoteSnapshots(Lead $lead): void
    {
        if ($lead->rdw_snapshot_json !== null) {
            return;
        }

        try {
            $quote = $this->quoteService->quote(
                $lead->kenteken,
                residencyChange: (bool) $lead->residency_change,
                autonomia: $lead->autonomia,
            );
        } catch (RdwUnavailableException $e) {
            Log::warning('Dossier aangemaakt zonder RDW-snapshot: RDW niet bereikbaar.', [
                'lead_id' => $lead->id,
                'exception' => $e->getMessage(),
            ]);

            return;
        } catch (Throwable $e) {
            report($e);

            return;
        }

        if (! $quote->found()) {
            return;
        }

        $lead->update([
            'rdw_snapshot_json' => $quote->rdwSnapshot(),
            'bpm_calculation_json' => $quote->bpmSnapshot(),
            'import_calculation_json' => $quote->importSnapshot(),
        ]);
    }
}
