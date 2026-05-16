<x-mail::message>
# Nieuwe lead #{{ $lead->id }}

**{{ $lead->name }}** heeft via de calculator een offerte aangevraagd.

## Contactgegevens

- **Naam:** {{ $lead->name }}
- **E-mail:** {{ $lead->email }}
- **Telefoon:** {{ $lead->phone }}
- **Woonplaats / regio Spanje:** {{ $lead->woonplaats_spanje }}
@if($lead->expected_move_date)
- **Verwachte verhuisdatum:** {{ $lead->expected_move_date }}
@endif

## Voertuig en pakket

- **Kenteken:** {{ $lead->kenteken }}
- **Pakket:** {{ $packageName }}@if($packagePriceEur) (€ {{ number_format($packagePriceEur, 0, ',', '.') }})@endif
- **Verhuizing residencia habitual:** {{ $lead->residency_change ? 'Ja' : 'Nee' }}
- **Autonomía:** {{ $lead->autonomia }}

## Indicatie uit calculator

@if($lead->bpm_teruggave_indicatie_eur !== null)
- **BPM-teruggave NL:** € {{ number_format($lead->bpm_teruggave_indicatie_eur, 0, ',', '.') }}
@endif
@if($lead->import_kosten_indicatie_eur !== null)
- **Spaanse importkosten:** € {{ number_format($lead->import_kosten_indicatie_eur, 0, ',', '.') }}
@endif
@if($lead->totaalprijs_indicatie_eur !== null)
- **Totaalprijs:** € {{ number_format($lead->totaalprijs_indicatie_eur, 0, ',', '.') }}
@endif

@if($lead->comment)
## Opmerking van de klant

> {{ $lead->comment }}
@endif

## Tracking

- **Bron:** {{ $lead->source }}
@if($lead->utm_source)
- **UTM:** {{ $lead->utm_source }} / {{ $lead->utm_medium ?? '—' }} / {{ $lead->utm_campaign ?? '—' }}
@endif
- **Aangevraagd op:** {{ $lead->created_at->format('d-m-Y H:i') }}

Reageer binnen 24 uur via {{ $lead->email }} of {{ $lead->phone }}.

— {{ config('app.name') }}
</x-mail::message>
