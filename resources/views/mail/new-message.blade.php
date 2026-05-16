<x-mail::message>
@if($recipientRole === 'admin')
# {{ $messages->count() === 1 ? 'Nieuw bericht' : $messages->count() . ' nieuwe berichten' }} van {{ $messages->first()->author->name ?? 'de klant' }}

Op dossier **#{{ $dossier->id }}** ({{ $dossier->merk }} {{ $dossier->model }} · {{ $dossier->kenteken }}):
@else
# {{ $messages->count() === 1 ? 'Nieuw bericht' : $messages->count() . ' nieuwe berichten' }} van de uitvoerder

Op uw dossier **#{{ $dossier->id }}**:
@endif

@foreach($messages as $message)
---

**{{ $message->author->name ?? 'Onbekend' }}** · {{ $message->created_at->format('d-m-Y H:i') }}

> {{ \Illuminate\Support\Str::of($message->body)->replace("\n", "\n> ") }}

@if($message->attachments->isNotEmpty())
*{{ $message->attachments->count() }} {{ $message->attachments->count() === 1 ? 'bijlage' : 'bijlagen' }}*: {{ $message->attachments->pluck('filename')->implode(', ') }}
@endif

@endforeach

---

<x-mail::button :url="$replyUrl" color="primary">
Antwoord op bericht
</x-mail::button>

@if($recipientRole === 'admin')
U kunt rechtstreeks vanuit het dossier antwoorden via het admin-paneel.
@else
U kunt vanuit uw klantportaal direct reageren.
@endif

— {{ config('app.name') }}
</x-mail::message>
