<x-mail::message>
# Bedankt voor uw aanvraag, {{ explode(' ', $lead->name)[0] }}

Uw offerteaanvraag is bij ons binnen onder referentienummer **#{{ $lead->id }}**.

Wij nemen **binnen 24 uur** contact met u op met een definitieve berekening en uitleg van de volgende stappen.

## Wat u heeft aangevraagd

- **Kenteken:** {{ $lead->kenteken }}
- **Pakket:** {{ $packageName }}@if($packagePriceEur) — € {{ number_format($packagePriceEur, 0, ',', '.') }}@endif
- **Regio in Spanje:** {{ $lead->woonplaats_spanje }}

@if($lead->totaalprijs_indicatie_eur !== null)
## Indicatie

Op basis van uw kenteken en pakketkeuze:

@if($lead->bpm_teruggave_indicatie_eur !== null && $lead->bpm_teruggave_indicatie_eur > 0)
- BPM-teruggave Nederland: **€ {{ number_format($lead->bpm_teruggave_indicatie_eur, 0, ',', '.') }}**
@endif
- Spaanse importkosten: **€ {{ number_format($lead->import_kosten_indicatie_eur ?? 0, 0, ',', '.') }}**
- Totaalprijs: **€ {{ number_format($lead->totaalprijs_indicatie_eur, 0, ',', '.') }}**

Dit zijn indicatieve bedragen. De definitieve berekening volgt in onze offerte op basis van de officiële Spaanse Hacienda-tabellen.
@endif

## Wat kunt u verwachten

1. Een van onze specialisten neemt binnen 24 uur contact op (werkdagen)
2. We bespreken uw situatie en lichten de stappen door
3. U ontvangt een schriftelijke offerte met alle definitieve bedragen
4. Pas daarna beslist u of u doorgaat — geen verplichtingen vooraf

Heeft u tussentijds vragen? Antwoord op deze e-mail of bel ons direct.

— {{ config('app.name') }}
</x-mail::message>
