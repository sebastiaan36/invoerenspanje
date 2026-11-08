{{--
    Pillar guide "Auto invoeren in Spanje". Rendered on the server and used as
    a prop for Gids.vue (v-html) and inside the crawler fallback HTML. Amounts
    come from config so the guide never drifts from the calculator.
--}}
@php
    $fixedCosts = collect(\App\Services\SpainImport\SpainFixedCosts::fromConfig())->keyBy('key');
    $brackets = config('spain_import.autonomias.costa_del_sol.iedmt_brackets');
    $packages = config('packages.list');
    $euro = fn (float $amount): string => '€ '.number_format($amount, fmod($amount, 1) ? 2 : 0, ',', '.');

    // Worked example: a car with a COC (no Ficha Técnica), temporary plates, below 120 g/km.
    $exampleKeys = ['tasas', 'honorarios', 'matriculas_temporales', 'itv_keuring'];
    $exampleSpanish = $fixedCosts->only($exampleKeys)->sum('amount_eur');
    $compleet = collect($packages)->firstWhere('slug', 'compleet');
@endphp

<p>
    Gaat u in Spanje wonen en wilt u uw Nederlandse auto meenemen? Dan <a href="/blog/wanneer-auto-op-spaans-kenteken-verplicht">moet de auto op een Spaans kenteken</a>. In deze
    gids leest u welke stappen er zijn, welke documenten u nodig heeft en wat het kost. Met de
    <a href="/#offerte">kostencheck</a> ziet u direct wat het voor uw eigen auto netto kost.
</p>

<h2>In het kort</h2>
<ul>
    <li>Het traject bestaat uit twee delen: <strong>export in Nederland</strong> (RDW en Belastingdienst) en
        <strong>inschrijving in Spanje</strong> (ITV-keuring en DGT).</li>
    <li>U krijgt in Nederland een deel van de BPM terug: de <strong>rest-BPM</strong>.</li>
    <li>In Spanje betaalt u vaste kosten en mogelijk <strong>registratiebelasting (IEDMT)</strong>. Auto's tot
        120 g/km CO₂ betalen geen IEDMT, en bij verhuizing van uw hoofdverblijf is vrijstelling mogelijk.</li>
    <li>Onze begeleiding kost een vaste prijs: van {{ $euro($packages[0]['price_eur']) }} tot
        {{ $euro(end($packages)['price_eur']) }}, afhankelijk van hoeveel u uit handen geeft.</li>
</ul>

<h2>Stap 1: de auto exporteren uit Nederland</h2>
<p>
    De auto wordt <a href="/blog/auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het">bij de RDW afgemeld</a> met de status export. U krijgt een exportkenteken dat 14 dagen geldig is,
    zodat u naar Spanje kunt rijden; u kunt de auto ook laten vervoeren. Lees de afwegingen in
    <a href="/blog/auto-naar-spanje-zelf-rijden-of-transport">zelf rijden of laten vervoeren</a>. Met de exportdocumenten
    vraagt u bij de Belastingdienst <a href="/blog/bpm-teruggave-auto-importeren-spanje">de rest-BPM terug</a>.
    Hoeveel dat is, hangt af van de oorspronkelijke BPM en de leeftijd van de auto: een auto van vijf jaar met
    € 6.000 oorspronkelijke BPM levert ongeveer € 2.100 op. Bereken het voor uw auto met de
    <a href="/bpm-calculator">BPM-calculator</a>.
</p>
<p>
    Let op de termijn: de auto moet binnen 13 weken na de exportmelding in Spanje zijn ingeschreven om de
    teruggave te kunnen aanvragen. Controleer de actuele voorwaarden bij de
    <a href="https://www.belastingdienst.nl/wps/wcm/connect/nl/bpm/content/teruggaaf-bpm-export-gebruikt-motorrijtuig" rel="noopener" target="_blank">Belastingdienst</a>.
</p>

<h2>Stap 2: documenten verzamelen</h2>
<p>Voor de inschrijving in Spanje (de matriculación) heeft u nodig:</p>
<ul>
    <li>een geldig paspoort of ID-kaart;</li>
    <li>uw NIE-nummer (Spaans fiscaal identificatienummer);</li>
    <li>het Nederlandse kentekenbewijs en de exportverklaring van de RDW;</li>
    <li>een COC-document (Certificaat van Overeenstemming), of anders een Ficha Técnica (technisch rapport,
        {{ $euro($fixedCosts['ficha_tecnica']['amount_eur']) }});</li>
    <li>een bewijs van adres in Spanje, zoals uw empadronamiento (inschrijving bij de gemeente) of een huurcontract.</li>
</ul>

<h2>Stap 3: tijdelijke kentekenplaten en de ITV-keuring</h2>
<p>
    Met tijdelijke kentekenplaten (matrículas temporales, {{ $euro($fixedCosts['matriculas_temporales']['amount_eur']) }})
    rijdt u legaal naar de keuring. Die zijn niet verplicht, maar wel nodig om met een geëxporteerde auto naar de ITV
    te rijden en te blijven rijden tot de definitieve platen er zijn. De <a href="/blog/itv-keuring-auto-import-spanje">ITV-keuring
    bij import</a> is de Spaanse APK; die betaalt u zelf aan de balie van het keuringsstation, ongeveer € 150 tot € 160.
    Let vooral op aanpassingen die niet bij de auto geregistreerd zijn, zoals
    <a href="/blog/auto-met-trekhaak-importeren-spanje">een later gemonteerde trekhaak</a> of
    <a href="/blog/getinte-ramen-ruitfolie-auto-spanje-itv">getinte ramen met ruitfolie</a>.
</p>

<h2>Stap 4: registratiebelasting (IEDMT) of vrijstelling</h2>
<p>
    De Impuesto Especial sobre Determinados Medios de Transporte (IEDMT) is de
    <a href="/blog/registratiebelasting-iedmt-spanje-vrijstelling-verhuizing">Spaanse registratiebelasting</a>. Het
    tarief hangt af van de CO₂-uitstoot en wordt berekend over de geschatte waarde van de auto:
</p>
<table>
    <thead>
        <tr><th>CO₂-uitstoot</th><th>Tarief</th></tr>
    </thead>
    <tbody>
        @php $previous = 0; @endphp
        @foreach ($brackets as $bracket)
            <tr>
                <td>
                    @if ($bracket['co2_max_g_per_km'] === null)
                        Meer dan {{ $previous }} g/km
                    @elseif ($previous === 0)
                        Tot en met {{ $bracket['co2_max_g_per_km'] }} g/km
                    @else
                        {{ $previous + 1 }} tot en met {{ $bracket['co2_max_g_per_km'] }} g/km
                    @endif
                </td>
                <td>{{ number_format($bracket['rate_pct'], 2, ',', '.') }}%</td>
            </tr>
            @php $previous = $bracket['co2_max_g_per_km'] ?? $previous; @endphp
        @endforeach
    </tbody>
</table>
<p>
    <strong>Verhuist u uw hoofdverblijf naar Spanje, dan kunt u onder voorwaarden vrijstelling van de IEDMT
    aanvragen.</strong> De auto moet dan onder meer al minimaal zes maanden op uw naam staan. Wij controleren of u in
    aanmerking komt en vragen de vrijstelling voor u aan. Wat de verhuizing betekent voor uw auto én uw inboedel,
    leest u in ons artikel over <a href="/blog/verhuisvrijstelling-spanje-inboedel-verhuisgoed-auto">de verhuisvrijstelling in Spanje</a>.
    Rijdt u elektrisch of hybride? Lees dan meer over
    <a href="/blog/belastingvrijstelling-elektrische-auto-hybride-spanje">het belastingvoordeel voor elektrische auto's en hybrides</a>.
</p>

<h2>Stap 5: inschrijving bij de DGT en Spaanse kentekenplaten</h2>
<p>
    Na de keuring dient de gestoría (het Spaanse administratiekantoor) de aanvraag in bij de DGT, de Spaanse
    tegenhanger van de RDW. U ontvangt <a href="/blog/spaans-kentekenbewijs-en-kentekenplaten">een Spaans kentekenbewijs en de
    definitieve Spaanse kentekenplaten</a>. Daarmee is de auto officieel Spaans.
</p>
<p>
    Daarna rijdt u als iedere Spaanse automobilist: de auto moet op vaste momenten naar de
    <a href="/blog/periodieke-itv-keuring-spanje">periodieke ITV-keuring</a>, en steeds meer steden regelen met het
    milieulabel van de DGT wie hun centrum in mag. Lees bijvoorbeeld over
    <a href="/blog/milieuzone-zbe-malaga-regels-auto">de milieuzone van Málaga</a>.
</p>

<h2>Wat kost auto invoeren in Spanje?</h2>
<p>Dit zijn de vaste kosten in Spanje, bovenop onze pakketprijs:</p>
<table>
    <thead>
        <tr><th>Kostenpost</th><th>Bedrag</th></tr>
    </thead>
    <tbody>
        @foreach ($fixedCosts as $cost)
            <tr><td>{{ $cost['label'] }}@if ($cost['optional']) (niet inbegrepen in de standaard kosten)@endif</td><td>{{ $euro($cost['amount_eur']) }}</td></tr>
        @endforeach
        <tr><td>Registratiebelasting (IEDMT)</td><td>0% tot 14,75%, afhankelijk van CO₂</td></tr>
    </tbody>
</table>
<p>
    <strong>Rekenvoorbeeld.</strong> Een auto met COC, uitstoot onder de 120 g/km en tijdelijke kentekenplaten: de
    Spaanse kosten zijn dan {{ $euro($exampleSpanish) }}. Met het pakket {{ $compleet['name'] }}
    ({{ $euro($compleet['price_eur']) }}) betaalt u in totaal {{ $euro($exampleSpanish + $compleet['price_eur']) }}.
    Daar staat de BPM-teruggave uit Nederland tegenover. Meer uitgewerkte voorbeelden vindt u in onze
    <a href="/blog/kosten-auto-invoeren-spanje-rekenvoorbeelden">drie rekenvoorbeelden</a>. Wat het voor uw auto netto
    betekent, ziet u met de <a href="/#offerte">kostencheck</a>.
</p>

<h2>Zelf doen of uitbesteden?</h2>
<p>
    U kunt veel zelf regelen, maar de timing tussen Nederland en Spanje luistert nauw. Wij bieden drie vaste pakketten:
</p>
<ul>
    @foreach ($packages as $package)
        <li><strong>{{ $package['name'] }} ({{ $euro($package['price_eur']) }})</strong>: {{ $package['tagline'] }}.</li>
    @endforeach
</ul>
<p>
    Bekijk wat elk pakket precies bevat op de <a href="/tarieven">tarievenpagina</a>, of lees meer over
    <a href="/diensten">onze diensten</a>.
</p>
