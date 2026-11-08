{!! $feedback->lead_name !!} (kenteken {!! $feedback->kenteken ?? 'onbekend' !!}) heeft gereageerd op de vraag waarom hij of zij niet voor ons koos:

{!! $feedback->message !!}

De feedback is opgeslagen in de backend:
{!! $feedbackUrl !!}

Beantwoord deze e-mail om rechtstreeks terug te schrijven naar {!! $feedback->lead_email !!}.
