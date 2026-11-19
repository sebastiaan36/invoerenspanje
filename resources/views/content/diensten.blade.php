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
                    'client' => 'U levert het Nederlandse kentekenbewijs aan. Rijdt u een leaseauto of een auto die nog niet is afbetaald, dan heeft u schriftelijke toestemming van de leasemaatschappij of financier nodig. Bij Basis meldt u de auto zelf af bij de RDW en bewaart u het uitschrijfbewijs, want dat is nodig in Spanje en voor de BPM-teruggave.',
                    'pitfalls' => 'Wie te vroeg afmeldt, komt in tijdnood: na de exportmelding is het exportkenteken veertien dagen geldig en moet de auto binnen 13 weken in Spanje zijn ingeschreven. Plan daarom eerst de reis en de Spaanse inschrijving. Vraag ook vooraf bij uw verzekeraar hoe u tijdens de rit gedekt bent.',
                    'links' => [
                        ['label' => 'Zo werkt de auto uitschrijven bij de RDW', 'href' => '/blog/auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het'],
                        ['label' => 'Leaseauto of gefinancierde auto meenemen', 'href' => '/blog/leaseauto-of-gefinancierde-auto-meenemen-naar-spanje'],
                    ],
                ],
                [
                    'id' => 'bpm-teruggave',
                    'title' => 'BPM-teruggave',
                    'description' => "Bij aankoop in Nederland is BPM (Belasting van Personenauto's en Motorrijwielen) betaald. Exporteert u de auto, dan heeft u recht op teruggave van de rest-BPM. Hoeveel dat is, hangt af van de leeftijd van de auto en de oorspronkelijke BPM. Bij Compleet en Compleet Plus dienen wij de aanvraag via onze exportpartner in bij de Belastingdienst. Bij Basis vraagt u de BPM-teruggave zelf aan.",
                    'includes' => [
                        'Indicatie van de rest-BPM vooraf',
                        'Aanvraag bij de Belastingdienst op basis van de exportmelding (Compleet en Compleet Plus)',
                        'Afstemming van de timing met de Spaanse inschrijving',
                    ],
                    'client' => 'Bij Compleet en Compleet Plus hoort de aanvraag bij de export via onze partner en hoeft u zelf niets in te dienen. Bij Basis dient u de aanvraag zelf in bij de Belastingdienst; wij geven vooraf de indicatie en stemmen de timing af. De auto moet wel aan de voorwaarden voldoen: het eerste Nederlandse kenteken is van op of na 16 oktober 2006 en de auto staat bij de RDW niet geregistreerd als schadevoertuig.',
                    'pitfalls' => 'Staat de auto niet binnen 13 weken na de exportmelding op een Spaans kenteken, dan vervalt het recht op teruggave. Een afgekeurde ITV of een ontbrekend document kan die termijn opeten. Wachten met exporteren kost ook geld, omdat de afschrijving elke maand doorloopt. Komt de rest-BPM uit onder € 50, dan keert de Belastingdienst niets uit.',
                    'links' => [
                        ['label' => 'BPM-teruggave bij export naar Spanje', 'href' => '/blog/bpm-teruggave-auto-importeren-spanje'],
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
                    'client' => 'U zet uw documenten in het klantenportaal: paspoort of ID-kaart, NIE-nummer, het Nederlandse kentekenbewijs, het COC-document als u dat heeft en een bewijs van adres in Spanje, zoals het empadronamiento of een huurcontract. Zodra de auto op Spaans kenteken staat, heeft u een Spaanse verzekering nodig. Bij Compleet en Compleet Plus verwijzen wij u door naar een aangesloten verzekeraar.',
                    'pitfalls' => 'Zonder NIE-nummer kan de inschrijving niet door, dus vraag dat op tijd aan. Voor de vrijstelling van de registratiebelasting moet de auto minimaal zes maanden op uw naam staan en vraagt u de vrijstelling aan binnen 60 dagen na uw empadronamiento. Houdt u alleen een tweede woning in Spanje aan, dan geldt de vrijstelling niet. Reken op vier tot acht weken van exportmelding tot kentekenbewijs.',
                    'links' => [
                        ['label' => 'De registratiebelasting (IEDMT) en de vrijstelling', 'href' => '/blog/registratiebelasting-iedmt-spanje-vrijstelling-verhuizing'],
                        ['label' => 'Het Spaanse kentekenbewijs en de platen', 'href' => '/blog/spaans-kentekenbewijs-en-kentekenplaten'],
                        ['label' => 'Stappenplan: Nederlandse auto op Spaans kenteken zetten', 'href' => '/auto-op-spaans-kenteken-zetten'],
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
                    'client' => 'De keuring betaalt u zelf aan de balie van het station, circa € 150 tot € 160. Bij Basis plant u de afspraak zelf en gaat u zelf. U neemt uw identiteitsbewijs, NIE-nummer, de Nederlandse kentekenpapieren en het COC-document mee. Met een uitgeschreven auto rijdt u op de groene tijdelijke platen naar het station.',
                    'pitfalls' => 'Het station vergelijkt de auto met de fabrieksgegevens. Een trekhaak of getinte ramen die niet bij de auto geregistreerd zijn, kunnen tot afkeuring leiden. Dat geldt ook voor een kapotte lamp of een versleten band. Laat de auto daarom eerst door een garage nakijken op de punten die de ITV controleert. Bij ernstige gebreken laat u die herstellen en volgt een herkeuring.',
                    'links' => [
                        ['label' => 'De ITV-keuring bij import', 'href' => '/blog/itv-keuring-auto-import-spanje'],
                        ['label' => 'Auto met trekhaak importeren', 'href' => '/blog/auto-met-trekhaak-importeren-spanje'],
                        ['label' => 'Getinte ramen en de ITV', 'href' => '/blog/getinte-ramen-ruitfolie-auto-spanje-itv'],
                    ],
                ],
            ],
        ],
    ];

    $addOns = [
        [
            'id' => 'transport',
            'title' => 'Transport naar Spanje',
            'body' => 'Wilt u niet zelf rijden? Bij Compleet Plus regelen wij het transport via onze transportpartners, van uw adres in Nederland naar uw adres in Spanje. De transportkosten zelf komen apart en hangen af van ophaal- en afleveradres. Rijdt u zelf, dan gebruikt u het exportkenteken, dat veertien dagen geldig is.',
            'link' => ['label' => 'Zelf rijden of laten vervoeren?', 'href' => '/blog/auto-naar-spanje-zelf-rijden-of-transport'],
        ],
        [
            'id' => 'aankoopbegeleiding',
            'title' => 'Aankoopbegeleiding',
            'body' => 'Koopt u nog een auto in Nederland om mee te nemen? In een adviesgesprek van 30 minuten kijken we naar CO₂-uitstoot (bepaalt de Spaanse registratiebelasting), leeftijd (bepaalt de BPM-teruggave) en of er een COC is. Inbegrepen bij Compleet Plus, of los af te nemen.',
            'link' => ['label' => 'Nieuwe auto meenemen: let op de btw', 'href' => '/blog/nieuwe-auto-meenemen-naar-spanje-btw'],
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
            <p class="mt-4 max-w-2xl text-muted-foreground">
                Per dienst leest u hieronder wat wij doen, wat u zelf aanlevert en waar het in de praktijk vaak
                misgaat. De officiële volgorde van RDW tot DGT staat in het
                <a href="/auto-op-spaans-kenteken-zetten" class="font-medium text-primary underline underline-offset-4">stappenplan om uw auto op Spaans kenteken te zetten</a>.
                Wat elk pakket kost, ziet u op de
                <a href="/tarieven" class="font-medium text-primary underline underline-offset-4">tarievenpagina</a>.
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
                                        <h5 class="mt-6 text-sm font-semibold text-foreground">Wat u zelf doet</h5>
                                        <p class="mt-1 leading-relaxed text-foreground/80">{{ $service['client'] }}</p>
                                        <h5 class="mt-5 text-sm font-semibold text-foreground">Waar het vaak misgaat</h5>
                                        <p class="mt-1 leading-relaxed text-foreground/80">{{ $service['pitfalls'] }}</p>
                                        <ul class="mt-4 space-y-1">
                                            @foreach ($service['links'] as $link)
                                                <li><a href="{{ $link['href'] }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-primary underline underline-offset-4 hover:text-primary/80">{{ $link['label'] }}</a></li>
                                            @endforeach
                                        </ul>
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
                        <p class="text-muted-foreground">
                            {{ $addOn['body'] }}
                            <a href="{{ $addOn['link']['href'] }}" class="font-medium text-primary underline underline-offset-4">{{ $addOn['link']['label'] }}</a>
                        </p>
                        <a href="/tarieven#pakketten" class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold whitespace-nowrap text-primary underline-offset-4 hover:underline">Bekijk Compleet Plus {!! $arrow !!}</a>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 max-w-2xl text-muted-foreground">
                Vragen over documenten, doorlooptijd of de kosten staan met antwoord bij de
                <a href="/veelgestelde-vragen" class="font-medium text-primary underline underline-offset-4">veelgestelde vragen</a>.
                Wilt u liever even overleggen, <a href="/contact" class="font-medium text-primary underline underline-offset-4">neem dan contact op</a>.
            </p>
        </div>
    </section>
</div>
