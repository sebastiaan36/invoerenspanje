{{--
    Pillar guide "Nederlandse auto op Spaans kenteken zetten" (stappenplan).
    Rendered on the server and used as a prop for Gids.vue (v-html) and inside
    the crawler fallback HTML. Amounts come from config so the guide never
    drifts from the calculator. Like the homepage, "vaste kosten" leave out the
    optional temporary plates; those are shown as a separate line.
--}}
@php
    $fixedCosts = collect(\App\Services\SpainImport\SpainFixedCosts::fromConfig())->keyBy('key');
    $includedFixedCosts = $fixedCosts->reject(fn (array $cost): bool => $cost['optional']);
    $optionalFixedCosts = $fixedCosts->filter(fn (array $cost): bool => $cost['optional']);
    $includedFixedCostsTotal = \App\Services\SpainImport\SpainFixedCosts::includedTotal();
    $brackets = config('spain_import.autonomias.costa_del_sol.iedmt_brackets');
    $packages = config('packages.list');
    $packagesBySlug = collect($packages)->keyBy('slug');
    $euro = fn (float $amount): string => '€ '.number_format($amount, fmod($amount, 1) ? 2 : 0, ',', '.');
    $percent = fn (float|int $rate): string => rtrim(rtrim(number_format((float) $rate, 2, ',', ''), '0'), ',').'%';
    $taxFreeUpTo = $brackets[0]['co2_max_g_per_km'];
    $temporaryPlates = $fixedCosts['matriculas_temporales']['amount_eur'];
    $fichaTecnica = $fixedCosts['ficha_tecnica']['amount_eur'];

    // Worked example: a car with a COC (no Ficha Técnica), below the 0% CO₂ limit, plus temporary plates.
    $exampleKeys = ['tasas', 'honorarios', 'itv_keuring'];
    $exampleSpanish = $fixedCosts->only($exampleKeys)->sum('amount_eur');
    $compleet = $packagesBySlug['compleet'];
@endphp

<p>
    Een Nederlandse auto op Spaans kenteken zetten gebeurt in twee delen. In Nederland meldt u de auto bij de RDW af
    voor export en vraagt u daarna de rest-BPM terug. In Spanje volgen de ITV-keuring, de registratiebelasting (IEDMT)
    en de inschrijving bij de DGT. Van de exportmelding tot het definitieve Spaanse kentekenbewijs duurt dat gemiddeld
    vier tot acht weken. De vaste kosten in Spanje zijn {{ $euro($includedFixedCostsTotal - $fichaTecnica) }} met een
    COC-document en {{ $euro($includedFixedCostsTotal) }} zonder, met daarbovenop eventueel IEDMT. Wat het voor uw eigen auto netto kost, ziet u met de <a href="/#offerte">kostencheck</a>.
</p>

<h2>In het kort</h2>
<ul>
    <li>Bent u fiscaal resident in Spanje (meer dan 183 dagen per jaar), dan moet uw auto op een Spaans kenteken.</li>
    <li>Regel vooraf uw NIE-nummer, een bewijs van adres in Spanje en bij voorkeur het COC-document van de auto.</li>
    <li>De volgorde is: exportmelding bij de RDW, de auto naar Spanje, tijdelijke groene platen, ITV-keuring, IEDMT of
        vrijstelling, inschrijving bij de DGT en tot slot de BPM-teruggave.</li>
    <li>De Spaanse inschrijving moet binnen 13 weken na de exportmelding rond zijn. Anders vervalt de BPM-teruggave.</li>
    <li>De registratiebelasting hangt af van de CO₂-uitstoot. Auto's tot
        {{ $taxFreeUpTo }} g/km CO₂ betalen geen IEDMT, en bij verhuizing van uw hoofdverblijf is vrijstelling mogelijk.</li>
    <li>Onze begeleiding kost een vaste prijs: van {{ $euro($packages[0]['price_eur']) }} tot
        {{ $euro(end($packages)['price_eur']) }}, afhankelijk van hoeveel u uit handen geeft.</li>
</ul>

<h2>Moet uw auto op een Spaans kenteken?</h2>
<p>
    Dat hangt af van waar u woont. Bent u fiscaal resident in Spanje, omdat u er meer dan 183 dagen per jaar verblijft,
    dan moet de auto op een Spaans kenteken. De Spaanse overheid geeft u daarvoor een maand vanaf het moment dat u
    resident bent. Lukt dat niet op tijd, dan kunt u bij de DGT een tijdelijke inschrijving aanvragen.
</p>
<ul>
    <li>U gaat in Spanje wonen: de auto moet op een Spaans kenteken.</li>
    <li>U overwintert en blijft in Nederland wonen: inschrijven is niet nodig zolang het verblijf tijdelijk blijft. Een
        EU-richtlijn noemt een maximum van zes maanden per twaalf maanden.</li>
    <li>U heeft een huis in Spanje, maar bent niet-resident: een Spaans kenteken is niet verplicht, wel mogelijk. U krijgt
        dan geen vrijstelling van de IEDMT.</li>
</ul>
<p>
    Bent u uit de Basisregistratie Personen (BRP) uitgeschreven, dan kunt u de auto niet zomaar op een Nederlands
    kenteken houden. Wat er per situatie geldt, leest u in ons artikel
    <a href="/blog/wanneer-auto-op-spaans-kenteken-verplicht">wanneer uw auto op een Spaans kenteken moet</a>.
</p>

<h2>Wat u vooraf regelt</h2>
<p>
    Een ontbrekend document betekent vaak een nieuwe afspraak en dus vertraging. Regel deze zaken daarom voordat u de
    auto in Nederland afmeldt.
</p>

<h3>NIE-nummer</h3>
<p>
    Het NIE (Número de Identificación de Extranjero) is uw Spaanse identificatienummer als buitenlander. U heeft het
    nodig voor vrijwel alle officiële handelingen in Spanje, ook voor het inschrijven van een auto. U vraagt het aan
    bij de Spaanse politie of via een advocaat.
</p>

<h3>Empadronamiento en residencia</h3>
<p>
    Met de empadronamiento schrijft u zich in bij het gemeentehuis van uw woonplaats. Die inschrijving dient ook als
    bewijs van uw adres in Spanje; een huurcontract kan daarvoor ook. Gaat u langer dan drie maanden in Spanje wonen,
    dan laat u zich als EU-burger inschrijven in het Registro Central de Extranjeros.
</p>

<h3>COC-document of Ficha Técnica</h3>
<p>
    Het COC-document (Certificaat van Overeenstemming) bevat de fabrieksgegevens van uw auto, waaronder de officiële
    CO₂-uitstoot. Het ITV-station neemt die gegevens over. Zonder COC is een Ficha Técnica nodig, een technisch rapport
    van {{ $euro($fichaTecnica) }}. Ook voor de IEDMT telt het: kan de CO₂-uitstoot niet worden aangetoond, dan geldt
    het hoogste tarief van {{ $percent(end($brackets)['rate_pct']) }}.
</p>

<h3>Toestemming bij lease of financiering</h3>
<p>
    Staat de auto op naam van een leasemaatschappij of is hij nog niet afbetaald, dan heeft u schriftelijke toestemming
    van de leasemaatschappij of financier nodig. Lees meer over
    <a href="/blog/leaseauto-of-gefinancierde-auto-meenemen-naar-spanje">een leaseauto of gefinancierde auto meenemen</a>.
</p>

<p>Voor de inschrijving in Spanje (de matriculación) verzamelt u uiteindelijk:</p>
<ul>
    <li>paspoort of ID-kaart, uw NIE-nummer en een bewijs van adres in Spanje;</li>
    <li>het Nederlandse kentekenbewijs en het uitschrijfbewijs van de RDW;</li>
    <li>het COC-document, of anders de Ficha Técnica;</li>
    <li>de aankoopfactuur of een ander eigendomsbewijs;</li>
    <li>het goedgekeurde ITV-rapport, het bewijs van IEDMT of vrijstelling en een bewijs van verzekering.</li>
</ul>

<h2>Stap 1: de auto afmelden bij de RDW (exportkenteken)</h2>
<p>
    De exportmelding doet u bij een RDW-keuringsstation, een RDW-balie of een erkend exportbedrijf. U neemt uw
    identiteitsbewijs, het kentekenbewijs en de kentekenplaten mee; de platen levert u in. De auto krijgt de status
    export. U ontvangt onder meer een vrijwaringsbewijs en het uitschrijfbewijs. Bewaar die goed, want u heeft ze nodig
    voor de inschrijving in Spanje en voor de BPM-teruggave.
</p>
<p>
    Met het exportkenteken (hetzelfde nummer op witte platen) mag u nog 14 dagen in Nederland rijden. In de landen onderweg gelden eigen regels voor een Nederlands exportkenteken.
</p>

<h3>Waarom u niet te vroeg afmeldt</h3>
<p>
    Met de exportmelding beginnen twee termijnen tegelijk: de 14 dagen voor het exportkenteken en de 13 weken waarbinnen
    de auto in Spanje ingeschreven moet zijn voor de BPM-teruggave. Plan daarom eerst de reis of het transport en de Spaanse stappen. Lang wachten
    heeft ook een prijs: de BPM schrijft elke maand verder af, dus u krijgt minder terug. Hoe de afmelding verloopt, leest u in
    <a href="/blog/auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het">de auto uitschrijven bij de RDW</a>.
</p>

<h2>Stap 2: de auto naar Spanje brengen</h2>
<p>
    U rijdt zelf of u laat de auto vervoeren. Rijdt u zelf, dan gaat de gebruikelijke route via België en Frankrijk.
    Naar de Costa del Sol is dat ruim 2.000 kilometer; reken op minimaal twee à drie reisdagen. Op een exportkenteken
    gelden de verzekeringsplicht en de APK-plicht nog gewoon. Bel daarom vóór de exportmelding uw verzekeraar en vraag of
    u onderweg in België, Frankrijk en Spanje gedekt bent, of u een groene kaart meekrijgt en wanneer uw Nederlandse polis
    precies stopt.
</p>
<p>
    Bij transport kiest u tussen open transport, meestal goedkoper, en gesloten transport voor nieuwe, dure of
    klassieke auto's. Vraag hoe de auto tijdens het
    vervoer verzekerd is en houd rekening met speling in de planning. In het Compleet Plus-pakket regelen wij het transport; de transportkosten
    komen apart en hoort u vooraf. Lees de afwegingen in
    <a href="/blog/auto-naar-spanje-zelf-rijden-of-transport">zelf rijden of laten vervoeren</a>.
</p>

<h2>Stap 3: tijdelijke groene platen (matrículas temporales)</h2>
<p>
    Het exportkenteken geldt alleen in Nederland. In Spanje rijdt u daarna met groene tijdelijke kentekenplaten, de
    matrículas temporales. Volgens de DGT mag u daarmee twee maanden rijden, met mogelijkheid tot verlenging. De platen
    zijn niet verplicht, maar wel nodig om met een geëxporteerde auto naar de ITV te rijden en te blijven rijden tot de
    definitieve platen er zijn. Bij ons kosten ze {{ $euro($temporaryPlates) }}. Stem af wanneer u aankomt en wanneer
    de platen klaar zijn, zodat de auto niet dagenlang stilstaat.
</p>
<p>
    De auto moet ook in deze periode verzekerd zijn. Rijden zonder verzekering kan in Spanje een boete van € 601 tot
    € 3.005 opleveren.
</p>

<h2>Stap 4: de ITV-keuring bij import</h2>
<p>
    Elke auto die voor het eerst in Spanje op kenteken komt, gaat langs de ITV, de Spaanse APK. Dat geldt ook als de
    Nederlandse APK nog geldig is. Het station vergelijkt de auto met de fabrieksgegevens uit het COC en controleert
    onder meer remmen, verlichting, banden en uitlaatgassen. De keuring gaat op afspraak (cita previa) en kost
    ca. € 150 tot € 160, afhankelijk van het station. U betaalt dat zelf aan de balie. Lees meer over
    <a href="/blog/itv-keuring-auto-import-spanje">de ITV-keuring bij import</a>.
</p>
<p>
    Lichte gebreken (defectos leves) houden de inschrijving niet direct tegen. Bij ernstige gebreken (defectos graves
    of muy graves) laat u de auto herstellen en volgt een herkeuring.
</p>

<h3>Waarop auto's vaak worden afgekeurd</h3>
<p>Het station keurt af wat afwijkt van de fabrieksgegevens, ook als het verder goed werkt. Let vooral op:</p>
<ul>
    <li>een later gemonteerde trekhaak. In Spanje moet een trekhaak op de tarjeta ITV staan. Een trekhaak die af fabriek
        op het COC staat, gaat meestal mee; een later gemonteerde geldt als wijziging (reforma). Zie <a href="/blog/auto-met-trekhaak-importeren-spanje">een auto met trekhaak importeren</a>.</li>
    <li>ruitfolie. Folie is verboden op de voorruit en de voorste zijruiten; fabrieksgetint glas met E-markering is
        goedgekeurd. Zie
        <a href="/blog/getinte-ramen-ruitfolie-auto-spanje-itv">getinte ramen en ruitfolie bij de ITV</a>.</li>
    <li>andere aanpassingen, zoals een bullbar, spoiler of aangepaste uitlaat, die niet officieel bij de auto zijn
        geregistreerd;</li>
    <li>kleine gebreken zoals een kapotte lamp of versleten band. Laat de auto vooraf door een garage nakijken.</li>
</ul>

<h2>Stap 5: registratiebelasting (IEDMT) of vrijstelling bij verhuizing</h2>
<p>
    De Impuesto Especial sobre Determinados Medios de Transporte (IEDMT) is een eenmalige
    <a href="/blog/registratiebelasting-iedmt-spanje-vrijstelling-verhuizing">Spaanse registratiebelasting</a> bij de
    eerste inschrijving. Het tarief hangt af van de officiële CO₂-uitstoot:
</p>
<ul>
    @php $previous = 0; @endphp
    @foreach ($brackets as $bracket)
        <li>
            @if ($bracket['co2_max_g_per_km'] === null)
                meer dan {{ $previous }} g/km:
            @elseif ($previous === 0)
                tot en met {{ $bracket['co2_max_g_per_km'] }} g/km:
            @else
                {{ $previous + 1 }} tot en met {{ $bracket['co2_max_g_per_km'] }} g/km:
            @endif
            {{ $percent($bracket['rate_pct']) }}
        </li>
        @php $previous = $bracket['co2_max_g_per_km'] ?? $previous; @endphp
    @endforeach
</ul>
<p>
    Bij een gebruikte auto betaalt u over de marktwaarde. Die volgt uit de tabellen met gemiddelde verkoopprijzen die
    het Spaanse ministerie van Financiën elk jaar publiceert. U betaalt met de
    aangifte Modelo 576, vóór de definitieve inschrijving. Ook bij 0% doet u aangifte voordat de auto op kenteken komt.
    Rijdt u elektrisch of hybride, lees dan over
    <a href="/blog/belastingvrijstelling-elektrische-auto-hybride-spanje">het belastingvoordeel voor elektrische auto's en hybrides</a>.
</p>

<h3>Vrijstelling bij verhuizing (Modelo 06)</h3>
<p>
    Verhuist u uw hoofdverblijf naar Spanje en neemt u uw eigen auto mee, dan kunt u vrijstelling krijgen. U vraagt die
    aan met Modelo 06 (code ET4) bij de Agencia Tributaria. De aangifte moet zijn goedgekeurd vóór de inschrijving bij de
    DGT. De voorwaarden:
</p>
<ul>
    <li>u woonde minimaal twaalf maanden achter elkaar buiten Spanje;</li>
    <li>de auto is in het land van herkomst op de normale manier belast;</li>
    <li>de auto staat minimaal zes maanden op uw naam;</li>
    <li>u vraagt de vrijstelling op tijd aan: binnen 60 dagen na uw inschrijving bij de gemeente
        (empadronamiento);</li>
    <li>u verkoopt de auto niet binnen twaalf maanden na de Spaanse inschrijving.</li>
</ul>
<p>
    Een tweede woning in Spanje telt niet: blijft uw hoofdverblijf in Nederland, dan betaalt u de IEDMT gewoon. De
    tweede voorwaarde vraagt aandacht, omdat u in Nederland de rest-BPM terugvraagt; hoe de belastingdienst dat
    beoordeelt, hangt af van uw dossier. Zie ook <a href="/blog/verhuisvrijstelling-spanje-inboedel-verhuisgoed-auto">de verhuisvrijstelling in Spanje</a>.
</p>

<h2>Stap 6: inschrijving bij de DGT en wegenbelasting (IVTM)</h2>
<p>
    Zijn de ITV en de IEDMT afgerond, dan dient de gestoría (het Spaanse administratiekantoor) de aanvraag in bij de
    DGT, de Spaanse tegenhanger van de RDW. De registratieleges van de DGT zijn
    {{ $euro($fixedCosts['tasas']['amount_eur']) }}. U ontvangt het permiso de circulación (het Spaanse
    kentekenbewijs) en de tarjeta ITV, het technische document van de auto.
</p>
<p>
    De platen maakt een erkende plaatmaker op vertoon van het kentekenbewijs. Loopt de inschrijving via ons, dan zitten
    de platen in de kosten van de gestoría. Meer over
    <a href="/blog/spaans-kentekenbewijs-en-kentekenplaten">het Spaanse kentekenbewijs en de kentekenplaten</a>.
</p>
<p>
    Vanaf dan betaalt u elk jaar wegenbelasting aan uw gemeente: het impuesto sobre vehículos de tracción mecánica
    (IVTM), ook wel impuesto de circulación. Gemeenten mogen tot 75% korting geven voor auto's die weinig vervuilen.
    Málaga geeft onder meer elektrische en hybride auto's 75% korting gedurende vijf belastingjaren na de inschrijving.
    Die korting vraagt u zelf aan bij Gestrisam, de belastingdienst van de gemeente. Elders gelden de regels van uw
    eigen gemeente. De Nederlandse
    motorrijtuigenbelasting stopt zodra de auto bij de RDW is uitgeschreven.
</p>

<h2>Stap 7: BPM-teruggave binnen 13 weken</h2>
<p>
    Met het Spaanse kentekenbewijs vraagt u bij de Belastingdienst de rest-BPM terug: het deel van de BPM dat nog in de
    waarde van de auto zit. Voorwaarden zijn dat de auto op of na 16 oktober 2006 zijn eerste Nederlandse kenteken kreeg,
    geen schadevoertuig is en binnen 13 weken na de exportmelding in Spanje is ingeschreven. U stuurt de exportdocumenten
    van de RDW en het Spaanse kentekenbewijs mee. De Belastingdienst heeft daarna 8 weken om te reageren. Controleer de
    actuele voorwaarden bij de
    <a href="https://www.belastingdienst.nl/wps/wcm/connect/nl/bpm/content/teruggaaf-bpm-export-gebruikt-motorrijtuig" rel="noopener" target="_blank">Belastingdienst</a>.
</p>
<p>
    Hoeveel u terugkrijgt, hangt af van de oorspronkelijke BPM en de leeftijd van de auto: een auto van vijf jaar met
    € 6.000 oorspronkelijke BPM levert ongeveer € 2.100 op. Komt de rest-BPM uit onder € 50, dan keert de
    Belastingdienst niets uit. Bereken het voor uw kenteken met de <a href="/bpm-calculator">BPM-calculator</a> en lees de details
    in <a href="/blog/bpm-teruggave-auto-importeren-spanje">BPM-teruggave bij export naar Spanje</a>.
</p>

<h2>Na de inschrijving</h2>
<p>
    Met Spaanse platen heeft u een <a href="/blog/auto-verzekeren-spanje-na-import">Spaanse autoverzekering</a> nodig.
    Zeg uw Nederlandse polis pas op als de Spaanse dekking ingaat, en vraag uw Nederlandse verzekeraar om een verklaring
    van uw schadeverleden; daar heeft u binnen 15 dagen recht op.
</p>
<p>
    De DGT deelt de auto na de inschrijving in een milieuklasse in (0, ECO, C, B of geen sticker). De sticker koopt u
    voor een paar euro bij onder meer Correos. Steden regelen daarmee de toegang tot hun milieuzone; lees over <a href="/blog/milieuzone-zbe-malaga-regels-auto">de milieuzone
    van Málaga</a>.
</p>
<p>
    Daarna volgt de <a href="/blog/periodieke-itv-keuring-spanje">periodieke ITV</a>. Een personenauto tot 4 jaar hoeft
    niet, van 4 tot 10 jaar gaat hij elke 2 jaar en daarna elk jaar. In Andalucía kost dat bij VEIASA ongeveer € 29 tot
    € 49. Uw Nederlandse rijbewijs hoeft u als EU-burger niet om te wisselen zolang het geldig is.
</p>

<h2>Hoe lang duurt het?</h2>
<p>
    Reken op gemiddeld vier tot acht weken van de exportmelding tot het definitieve Spaanse kentekenbewijs. De planning
    van de ITV-keuring en de verwerking bij de DGT bepalen grotendeels hoe lang het duurt. Een herkeuring, een
    ontbrekend document of een transport dat later rijdt, maken het traject langer. Deze termijnen staan vast:
</p>
<ul>
    <li>14 dagen rijden in Nederland op het exportkenteken;</li>
    <li>13 weken vanaf de exportmelding om de auto in Spanje in te schrijven, voor de BPM-teruggave;</li>
    <li>twee maanden rijden op de tijdelijke groene platen, met mogelijkheid tot verlenging;</li>
    <li>8 weken waarin de Belastingdienst op uw BPM-verzoek reageert.</li>
</ul>

<h2>Wat kost het?</h2>
<p>
    De vaste kosten in Spanje betaalt iedereen, ook wie vrijstelling van de IEDMT krijgt. De tijdelijke platen zijn
    optioneel en tellen daarom apart.
</p>
<table>
    <thead>
        <tr><th>Kostenpost</th><th>Bedrag</th></tr>
    </thead>
    <tbody>
        @foreach ($includedFixedCosts as $cost)
            <tr><td>{{ $cost['label'] }}@if ($cost['key'] === 'ficha_tecnica') (vervalt met een COC-document)@endif</td><td>{{ $euro($cost['amount_eur']) }}</td></tr>
        @endforeach
        <tr><td>Vaste kosten in Spanje samen (zonder COC)</td><td>{{ $euro($includedFixedCostsTotal) }}</td></tr>
        @foreach ($optionalFixedCosts as $cost)
            <tr><td>{{ $cost['label'] }}, los van de vaste kosten</td><td>{{ $euro($cost['amount_eur']) }}</td></tr>
        @endforeach
        <tr><td>Registratiebelasting (IEDMT)</td><td>{{ $percent($brackets[0]['rate_pct']) }} tot {{ $percent(end($brackets)['rate_pct']) }}, afhankelijk van CO₂</td></tr>
        <tr><td>Onze begeleiding</td><td>{{ $euro($packages[0]['price_eur']) }} tot {{ $euro(end($packages)['price_eur']) }}</td></tr>
    </tbody>
</table>
<p>
    Een rekenvoorbeeld: een auto met COC en een uitstoot tot {{ $taxFreeUpTo }} g/km heeft
    {{ $euro($exampleSpanish) }} aan vaste kosten in Spanje. Met tijdelijke platen ({{ $euro($temporaryPlates) }}) en het
    pakket {{ $compleet['name'] }} ({{ $euro($compleet['price_eur']) }}) betaalt u in totaal
    {{ $euro($exampleSpanish + $temporaryPlates + $compleet['price_eur']) }}. Daar gaat de BPM-teruggave weer vanaf. Meer
    uitgewerkte voorbeelden staan in <a href="/blog/kosten-auto-invoeren-spanje-rekenvoorbeelden">drie rekenvoorbeelden</a>,
    alle bedragen op de <a href="/tarieven">tarievenpagina</a>.
</p>

<h2>Zelf doen, via een gestoría of met ons?</h2>
<p>
    U kunt de export zelf doen en in Spanje zelf een gestoría zoeken. Dan coördineert u beide landen zelf, met de
    papieren in het Spaans. De leges en de ITV betaalt u in elk geval; de gestoría
    rekent een eigen tarief. Wij rekenen met het tarief van onze partner-gestoría,
    {{ $euro($fixedCosts['honorarios']['amount_eur']) }} inclusief de definitieve platen. Kiest u een van onze pakketten,
    dan komt daar een vaste prijs bij.
</p>
<table>
    <thead>
        <tr><th>Aanpak</th><th>Prijs begeleiding</th><th>Export in Nederland</th><th>ITV-keuring</th><th>Transport</th></tr>
    </thead>
    <tbody>
        <tr><td>Zelf, met een eigen gestoría</td><td>Tarief van de gestoría</td><td>Zelf</td><td>Zelf afspraak maken en gaan</td><td>Zelf</td></tr>
        <tr><td>{{ $packagesBySlug['basis']['name'] }}</td><td>{{ $euro($packagesBySlug['basis']['price_eur']) }}</td><td>Zelf</td><td>Zelf afspraak maken en gaan</td><td>Zelf</td></tr>
        <tr><td>{{ $packagesBySlug['compleet']['name'] }}</td><td>{{ $euro($packagesBySlug['compleet']['price_eur']) }}</td><td>Via onze partner, met BPM-aanvraag</td><td>Wij plannen, u gaat</td><td>Zelf</td></tr>
        <tr><td>{{ $packagesBySlug['compleet_plus']['name'] }}</td><td>{{ $euro($packagesBySlug['compleet_plus']['price_eur']) }}</td><td>Via onze partner, met BPM-aanvraag</td><td>Wij gaan namens u</td><td>Wij regelen het, transportkosten apart</td></tr>
    </tbody>
</table>
<p>
    In het pakket {{ $packagesBySlug['basis']['name'] }} levert u de documenten en het COC aan en verwerken wij de
    aanvraag bij de DGT; de groene platen en daarna het officiële kenteken haalt u bij ons op. In
    {{ $packagesBySlug['compleet']['name'] }} regelen wij ook de Ficha Técnica als het COC ontbreekt en verwijzen wij u
    door naar een aangesloten verzekeraar. De Spaanse vaste kosten en de IEDMT zitten in geen enkel pakket. Maikel kent
    de Spaanse kant en is uw vaste aanspreekpunt; Sebastiaan houdt uw dossier online bij. Bekijk alle pakketten op de <a href="/tarieven">tarievenpagina</a> of lees
    over <a href="/diensten">onze diensten</a>.
</p>
<p>
    Op de homepage ziet u wat het kost om <a href="/">auto invoeren in Spanje uit handen te geven</a>, met de
    BPM-teruggave al verrekend.
</p>
