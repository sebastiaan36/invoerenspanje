{{--
    Body copy of the Over ons page. Rendered once on the server and used
    twice: as `contentHtml` for OverOns.vue (v-html) and inside the crawler
    fallback HTML. The hero and the closing call to action live in the Vue page.
    The person ids match the Person @id's in partials/schema.blade.php.
--}}
@php
    $check = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-1 size-4 shrink-0 stroke-[3] text-success" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';

    $team = [
        [
            'id' => 'maikel',
            'name' => 'Maikel',
            'photo' => '/images/maikel.jpeg',
            'alt' => 'Maikel, autospecialist',
            'company' => 'Maikel Mobility',
            'role' => 'Autospecialist, regelt de Spaanse kant van het traject',
            'bio' => 'Maikel is de autospecialist achter het importtraject. Hij studeerde HBO Automotive aan de IVA, werkt met erkende RDW-exportpartners en kent de procedures bij de DGT (de Spaanse RDW). Van Ficha Técnica (Spaans technisch voertuigrapport) tot ITV (de Spaanse APK-keuring): Maikel regelt het.',
            'highlights' => [
                'IVA-gecertificeerd: HBO Automotive aan de IVA',
                'Werkt met erkende RDW-exportpartners',
                'Kent de procedures bij de DGT (de Spaanse RDW)',
            ],
        ],
        [
            'id' => 'sebastiaan',
            'name' => 'Sebastiaan',
            'photo' => '/images/sebastiaan.jpeg',
            'alt' => 'Sebastiaan, online aanspreekpunt',
            'company' => 'Been & van Dam',
            'role' => 'Uw aanspreekpunt online',
            'bio' => 'Sebastiaan is uw aanspreekpunt online. Hij beantwoordt uw vragen over de berekening en de offerte, en houdt uw dossier bij, zodat u steeds weet hoe het met uw auto staat.',
            'highlights' => [
                'Beantwoordt uw vragen over berekening en offerte',
                'Houdt uw dossier en de berekening voor u bij',
            ],
        ],
    ];

    $values = [
        [
            'title' => 'Alles uit één hand, van Nederland tot Spanje',
            'body' => 'De BPM-teruggave en export in Nederland, en de inschrijving op een Spaans kenteken (matriculación) en de keuring (ITV) in Spanje: één team regelt beide kanten. U hoeft zelf geen Nederlandse exporteur en Spaanse gestoría (administratiekantoor) te combineren.',
        ],
        [
            'title' => 'Nederlandstalig, met vaste prijzen',
            'body' => 'U spreekt met ons gewoon Nederlands, ook over de Spaanse kant. U kiest vooraf een pakket met een vaste prijs, zodat u weet waar u aan toe bent voordat u akkoord gaat.',
        ],
    ];
@endphp
<div class="not-prose">
    <section class="border-t border-border bg-muted/40">
        <div class="container mx-auto max-w-3xl px-4 py-16 text-center">
            <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">Onze missie</h2>
            <p class="mx-auto mt-5 max-w-2xl text-muted-foreground">
                Veel Nederlanders wonen of verblijven langdurig aan de Costa del Sol. Zij willen hun vertrouwde auto
                meenemen naar Spanje, maar lopen vast op bureaucratie, taalbarrières en een ondoorzichtig systeem. Wij
                zetten dat recht: één aanspreekpunt, vaste prijs, alles geregeld.
            </p>
        </div>
    </section>

    <section class="border-t border-border bg-background">
        <div class="container mx-auto max-w-5xl px-4 py-16">
            <div class="text-center">
                <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">Het team</h2>
                <p class="mx-auto mt-3 max-w-xl text-muted-foreground">
                    Twee mensen met elk hun eigen vakgebied. Samen zorgen zij voor het complete plaatje.
                </p>
            </div>

            <div class="mt-12 grid gap-8 lg:grid-cols-2">
                @foreach ($team as $member)
                    <article id="{{ $member['id'] }}" class="flex scroll-mt-24 flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
                        <div class="h-64 w-full overflow-hidden bg-muted sm:h-80 lg:h-96">
                            <img src="{{ $member['photo'] }}" alt="{{ $member['alt'] }}" width="800" height="800" loading="lazy" class="h-full w-full object-cover object-top">
                        </div>

                        <div class="flex flex-col p-6 sm:p-8">
                            <div>
                                <h3 class="font-display text-xl font-semibold text-foreground">{{ $member['name'] }}</h3>
                                <p class="text-sm font-medium text-foreground">{{ $member['company'] }}</p>
                                <p class="text-sm text-muted-foreground">{{ $member['role'] }}</p>
                            </div>

                            <p class="mt-4 leading-relaxed text-foreground/80">{{ $member['bio'] }}</p>

                            <ul class="mt-5 space-y-2">
                                @foreach ($member['highlights'] as $highlight)
                                    <li class="flex items-start gap-2.5">{!! $check !!}<span class="text-foreground">{{ $highlight }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-t border-border bg-muted/40">
        <div class="container mx-auto max-w-5xl px-4 py-16">
            <h2 class="text-center font-display text-2xl font-semibold text-foreground sm:text-3xl">Waar wij voor staan</h2>

            <div class="mt-10 grid gap-10 md:grid-cols-2 md:gap-12">
                @foreach ($values as $value)
                    <div class="border-t border-border pt-6">
                        <h3 class="font-display text-xl font-semibold text-foreground">{{ $value['title'] }}</h3>
                        <p class="mt-3 leading-relaxed text-muted-foreground">{{ $value['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
