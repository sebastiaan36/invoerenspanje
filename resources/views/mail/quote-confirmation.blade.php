<x-mail::message>
# Bedankt voor uw aanvraag, {{ explode(' ', $lead->name)[0] }}

Uw offerteaanvraag is bij ons binnen onder referentienummer **#{{ $lead->id }}**.

Wij nemen **binnen 24 uur** contact met u op (op werkdagen) met een definitieve berekening en uitleg van de volgende stappen.

## Uw gegevens

- **Kenteken:** {{ $lead->kenteken }}
- **Pakket:** {{ $packageName }}@if($packagePriceEur) — € {{ number_format($packagePriceEur, 0, ',', '.') }}@endif
- **Regio in Spanje:** {{ $lead->woonplaats_spanje }}

Heeft u tussentijds vragen? Antwoord gerust op deze e-mail.

— {{ config('app.name') }}
</x-mail::message>
