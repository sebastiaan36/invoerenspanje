<x-mail::message>
# Nieuw contactbericht

**Naam:** {{ $senderName }}
**E-mail:** {{ $senderEmail }}
@if($senderPhone)
**Telefoon:** {{ $senderPhone }}
@endif
@if($contactSubject)
**Onderwerp:** {{ $contactSubject }}
@endif

---

{{ $contactMessage }}

---

*Beantwoord deze e-mail rechtstreeks om terug te schrijven naar {{ $senderName }}.*
</x-mail::message>
