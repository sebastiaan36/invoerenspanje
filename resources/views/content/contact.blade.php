{{--
    Contact details for the crawler fallback HTML of /contact (included by
    seo/fallback.blade.php). Contact.vue shows the same facts from the same
    sources: config/contact.php (shared `contact` prop) and config/werkgebied.php.
--}}
<h2>Bel of WhatsApp</h2>
<p>
    U spreekt met Maikel of Sebastiaan, gewoon in het Nederlands. Telefoon en WhatsApp:
    <a href="tel:{{ config('contact.phone') }}">{{ config('contact.phone_display') }}</a>
    (<a href="{{ config('contact.whatsapp_url') }}">stuur een WhatsApp-bericht</a>).
</p>

<h2>E-mail</h2>
<p><a href="mailto:{{ config('contact.email') }}">{{ config('contact.email') }}</a>. Wij reageren binnen één werkdag.</p>

<h2>Bereikbaarheid (Spaanse tijd)</h2>
<ul>
    @foreach (config('contact.opening_hours') as $openingHours)
        <li>{{ $openingHours['days'] }}: {{ $openingHours['hours'] }}</li>
    @endforeach
</ul>

<h2>Ons werkgebied</h2>
<p>{{ config('werkgebied.intro') }}</p>
<ul>
    @foreach (config('werkgebied.plaatsen') as $plaats)
        <li>{{ $plaats['naam'] }}</li>
    @endforeach
</ul>
