<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Mail\WelcomeKlantMail;
use App\Models\Dossier;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class LeadConverter
{
    /**
     * Maak op basis van een lead een (eventueel bestaande) user + dossier aan en
     * stuur een welkomstmail met set-password link.
     */
    public function convert(Lead $lead, ?int $serviceFeeEur = null): Dossier
    {
        return DB::transaction(function () use ($lead, $serviceFeeEur): Dossier {
            $user = User::firstOrCreate(
                ['email' => $lead->email],
                [
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'role' => 'klant',
                    // Sterk random wachtwoord — klant zet zelf via reset-link.
                    'password' => bcrypt(Str::random(48)),
                    'email_verified_at' => now(),
                ],
            );

            // Vul ontbrekende velden bij bestaande user.
            $userDirty = false;
            if ($user->phone === null && $lead->phone) {
                $user->phone = $lead->phone;
                $userDirty = true;
            }
            if (! $user->isAdmin() && $user->role !== 'klant') {
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
                'co2' => $fuel['co2_gecombineerd'] ?? null,
                'rdw_data_json' => $rdw,
                'pakket' => $lead->package_slug,
                'bpm_indicatie_eur' => $lead->bpm_teruggave_indicatie_eur,
                'bpm_calculation_json' => $lead->bpm_calculation_json,
                'import_calculation_json' => $lead->import_calculation_json,
                'service_fee_eur' => $serviceFeeEur,
                'started_at' => now(),
            ]);

            // Lead afsluiten als gewonnen.
            $lead->update(['status' => 'gewonnen']);

            // Reset-token aanmaken zodat de klant zelf een wachtwoord kan zetten.
            $token = Password::broker()->createToken($user);

            Mail::to($user->email)->send(new WelcomeKlantMail($user, $dossier, $token));

            return $dossier;
        });
    }
}
