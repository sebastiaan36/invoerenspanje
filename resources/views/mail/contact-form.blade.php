<x-mail::message>
# Nieuw contactbericht

**Naam:** {{ $mailable->senderName }}
**E-mail:** {{ $mailable->senderEmail }}
@if($mailable->senderPhone)
**Telefoon:** {{ $mailable->senderPhone }}
@endif
@if($mailable->subject)
**Onderwerp:** {{ $mailable->subject }}
@endif

---

{{ $mailable->message }}

---

*Beantwoord deze e-mail rechtstreeks om terug te schrijven naar {{ $mailable->senderName }}.*
</x-mail::message>
