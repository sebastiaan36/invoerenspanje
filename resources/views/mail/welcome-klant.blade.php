<x-mail::message>
# Welkom, {{ explode(' ', $user->name)[0] }}

Uw aanvraag is door ons geaccepteerd en uw persoonlijke dossier staat klaar onder referentienummer **#{{ $dossier->id }}**.

## Toegang tot uw klantportaal

Wij hebben een account voor u aangemaakt op **{{ $user->email }}**. Klik op de knop hieronder om uw wachtwoord in te stellen en in te loggen.

<x-mail::button :url="url($setPasswordUrl)" color="primary">
Stel uw wachtwoord in
</x-mail::button>

Vanuit het portaal kunt u:

- de status van uw dossier volgen
- documenten uploaden (paspoort, NIE, kentekenbewijs, COC)
- berichten uitwisselen met onze uitvoerder

## Uw dossier

- **Kenteken:** {{ $dossier->kenteken }}
- **Pakket:** {{ $dossier->pakket }}
@if($dossier->bpm_indicatie_eur)
- **BPM-teruggave indicatie:** € {{ number_format($dossier->bpm_indicatie_eur, 0, ',', '.') }}
@endif
@if($dossier->service_fee_eur)
- **Servicebedrag:** € {{ number_format($dossier->service_fee_eur, 0, ',', '.') }}
@endif

Heeft u vragen? Beantwoord deze e-mail of bel ons direct.

— {{ config('app.name') }}
</x-mail::message>
