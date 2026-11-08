{{--
    Body copy of the Diensten page. Rendered once on the server and used
    twice: as `contentHtml` for Diensten.vue (v-html) and inside the crawler
    fallback HTML. The hero and the closing call to action live in the Vue page.
--}}
@php
    $check = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-1 size-4 shrink-0 stroke-[3] text-success" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    $arrow = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';
    $fichaTecnica = collect(config('spain_import.fixed_costs'))->firstWhere('key', 'ficha_tecnica');

    $lanes = [
        [
            'country' => 'Nederland',
            'authorities' => 'RDW en Belastingdienst',
            'dot' => 'bg-primary',
            'services' => [
                [
                    'id' => 'auto-export-nederland',
                    'title' => 'Auto-export Nederland',
                    'description' => 'Voordat uw auto in Spanje ingeschreven kan worden, moet hij in Nederland worden afgemeld met de status export. Bij Compleet en Compleet Plus regelen wij dit via een van onze erkende RDW-exportpartners. Bij Basis regelt u de export zelf en sluiten wij daarop aan.',
                    'includes' => [
                        'Exportmelding via een erkende RDW-exportpartner',
                        'Exportkenteken, veertien dagen geldig voor de rit naar Spanje',
                        'Exportdocumenten die de DGT (de Spaanse RDW) vraagt',
                    ],
                ],
                [
                    'id' => 'bpm-teruggave',
                    'title' => 'BPM-teruggave',
                    'description' => "Bij aankoop in Nederland is BPM (Belasting van Personenauto's en Motorrijwielen) betaald. Exporteert u de auto, dan heeft u recht op teruggave van de rest-BPM. Hoeveel dat is, hangt af van de leeftijd van de auto en de oorspronkelijke BPM. Wij dienen de aanvraag in bij de Belastingdienst.",
                    'includes' => [
                        'Indicatie van de rest-BPM vooraf',
                        'Aanvraag bij de Belastingdienst op basis van de exportmelding',
                        'Afstemming van de timing met de Spaanse inschrijving',
                    ],
                    'cta' => ['label' => 'Bereken uw BPM-teruggave', 'href' => '/bpm-calculator'],
                ],
            ],
        ],
        [
            'country' => 'Spanje',
            'authorities' => 'DGT en ITV',
            'dot' => 'bg-accent',
            'services' => [
                [
                    'id' => 'auto-op-spaans-kenteken',
                    'title' => 'Auto op Spaans kenteken',
                    'description' => 'Woont u meer dan 183 dagen per jaar in Spanje, dan moet uw auto op Spaans kenteken. Wij verzorgen de matriculación (de Spaanse inschrijving) bij de DGT, van de documenten tot de definitieve platen. U heeft er geen Spaans voor nodig.',
                    'includes' => [
                        'Controle van uw documenten, zoals uw NIE (Spaans fiscaal nummer) en empadronamiento (inschrijving bij de gemeente)',
                        "Verwerking van uw COC, of een Ficha Técnica (technisch rapport) als er geen COC is, voor €\u{00A0}".number_format((float) ($fichaTecnica['amount_eur'] ?? 0), 0, ',', '.').' extra',
                        'Matrículas temporales: groene tijdelijke kentekenplaten om legaal naar de ITV te rijden (optioneel)',
                        'Berekening van de registratiebelasting (IEDMT) of de vrijstelling bij verhuizing',
                        'Aanvraag bij de DGT en communicatie in het Spaans',
                        'Definitieve Spaanse kentekenplaten',
                    ],
                ],
                [
                    'id' => 'itv-begeleiding',
                    'title' => 'ITV-begeleiding',
                    'description' => 'De ITV (Inspección Técnica de Vehículos) is de Spaanse APK en een verplicht onderdeel van de inschrijving. Bij Compleet en Compleet Plus plannen wij de afspraak bij een erkend keuringsstation. Bij Compleet Plus gaan wij namens u naar de keuring.',
                    'includes' => [
                        'Afspraak bij een erkend ITV-station in uw buurt',
                        'Checklist met de documenten die u meeneemt',
                        'Uitleg over het keuringsproces en hulp bij een herkeuring',
                        'Namens u aanwezig bij de keuring (Compleet Plus)',
                    ],
                ],
            ],
        ],
    ];

    $addOns = [
        [
            'id' => 'transport',
            'title' => 'Transport naar Spanje',
            'body' => 'Wilt u niet zelf rijden? Bij Compleet Plus regelen wij het transport via onze transportpartners, van uw adres in Nederland naar uw adres in Spanje. De transportkosten zelf komen apart en hangen af van ophaal- en afleveradres.',
        ],
        [
            'id' => 'aankoopbegeleiding',
            'title' => 'Aankoopbegeleiding',
            'body' => 'Koopt u nog een auto in Nederland om mee te nemen? In een adviesgesprek van 30 minuten kijken we naar CO₂-uitstoot (bepaalt de Spaanse registratiebelasting), leeftijd (bepaalt de BPM-teruggave) en of er een COC is. Inbegrepen bij Compleet Plus, of los af te nemen.',
        ],
    ];
@endphp
<div class="not-prose">
    <section class="border-t border-border bg-card" aria-labelledby="dossier-title">
        <div class="container mx-auto max-w-5xl px-4 py-16 md:py-24">
            <h2 id="dossier-title" class="max-w-2xl font-display text-3xl font-semibold text-balance text-foreground md:text-4xl">Wat wij voor u regelen</h2>
            <p class="mt-4 max-w-2xl text-lg text-muted-foreground">
                Uw auto doorloopt een traject in Nederland en een traject in Spanje. Wij stemmen beide op elkaar af,
                zodat u één aanspreekpunt heeft. Welke stappen wij overnemen, hangt af van uw pakket.
            </p>

            <div class="mt-12 space-y-16">
                @foreach ($lanes as $lane)
                    <div>
                        <h3 class="flex flex-wrap items-baseline gap-x-3 gap-y-1 border-b border-border pb-4 font-display text-2xl font-semibold text-foreground">
                            <span class="size-2.5 shrink-0 self-center rounded-full {{ $lane['dot'] }}" aria-hidden="true"></span>
                            {{ $lane['country'] }}
                            <span class="font-sans text-sm font-normal text-muted-foreground">{{ $lane['authorities'] }}</span>
                        </h3>

                        <div class="divide-y divide-border">
                            @foreach ($lane['services'] as $service)
                                <article id="{{ $service['id'] }}" class="grid scroll-mt-24 gap-6 py-10 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] md:gap-12">
                                    <div>
                                        <h4 class="font-display text-xl font-semibold text-foreground">{{ $service['title'] }}</h4>
                                        <p class="mt-3 leading-relaxed text-foreground/80">{{ $service['description'] }}</p>
                                        @isset($service['cta'])
                                            <a href="{{ $service['cta']['href'] }}" class="mt-3 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary underline-offset-4 hover:underline">{{ $service['cta']['label'] }} {!! $arrow !!}</a>
                                        @endisset
                                    </div>
                                    <ul class="space-y-3">
                                        @foreach ($service['includes'] as $item)
                                            <li class="flex items-start gap-3">{!! $check !!}<span class="text-foreground">{{ $item }}</span></li>
                                        @endforeach
                                    </ul>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-t border-border bg-secondary/30" aria-labelledby="addons-title">
        <div class="container mx-auto max-w-5xl px-4 py-16">
            <h2 id="addons-title" class="font-display text-2xl font-semibold text-foreground sm:text-3xl">Daarnaast mogelijk</h2>
            <div class="mt-8 divide-y divide-border border-y border-border">
                @foreach ($addOns as $addOn)
                    <div id="{{ $addOn['id'] }}" class="grid scroll-mt-24 gap-3 py-6 md:grid-cols-[12rem_minmax(0,1fr)_auto] md:items-center md:gap-8">
                        <h3 class="font-semibold text-foreground">{{ $addOn['title'] }}</h3>
                        <p class="text-muted-foreground">{{ $addOn['body'] }}</p>
                        <a href="/tarieven#pakketten" class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold whitespace-nowrap text-primary underline-offset-4 hover:underline">Bekijk Compleet Plus {!! $arrow !!}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
