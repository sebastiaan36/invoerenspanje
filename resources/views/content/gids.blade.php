{{--
    Pillar guide "Auto invoeren in Spanje". Rendered on the server and used as
    a prop for Gids.vue (v-html) and inside the crawler fallback HTML. Amounts
    come from config so the guide never drifts from the calculator.
--}}
@php
    $fixedCosts = collect(config('spain_import.fixed_costs'))->keyBy('key');
    $brackets = config('spain_import.autonomias.costa_del_sol.iedmt_brackets');
    $packages = config('packages.list');
    $euro = fn (float $amount): string => '€ '.number_format($amount, fmod($amount, 1) ? 2 : 0, ',', '.');

    // Worked example: a car with a COC (no Ficha Técnica), temporary plates, below 120 g/km.
    $exampleKeys = ['tasas', 'placas_definitivas', 'honorarios', 'matriculas_temporales', 'itv_keuring'];
    $exampleSpanish = $fixedCosts->only($exampleKeys)->sum('amount_eur');
    $compleet = collect($packages)->firstWhere('slug', 'compleet');
@endphp

<p>
    Gaat u in Spanje wonen en wilt u uw Nederlandse auto meenemen? Dan moet de auto op een Spaans kenteken. In deze
    gids leest u welke stappen er zijn, welke documenten u nodig heeft en wat het kost. Met de
    <a href="/#offerte">kentekencheck</a> ziet u direct wat het voor uw eigen auto netto kost.
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
    De auto wordt bij de RDW afgemeld met de status export. U krijgt een exportkenteken dat 14 dagen geldig is,
    zodat u naar Spanje kunt rijden. Met de exportdocumenten vraagt u bij de Belastingdienst de rest-BPM terug.
    Hoeveel dat is, hangt af van de oorspronkelijke BPM en de leeftijd van de auto: een auto van vijf jaar met
    € 6.000 oorspronkelijke BPM levert ongeveer € 1.800 op. Bereken het voor uw auto met de
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
    te rijden en te blijven rijden tot de definitieve platen er zijn. De ITV-keuring is de Spaanse APK; die betaalt u
    zelf aan de balie van het keuringsstation, ongeveer € 150 tot € 160.
</p>

<h2>Stap 4: registratiebelasting (IEDMT) of vrijstelling</h2>
<p>
    De Impuesto Especial sobre Determinados Medios de Transporte (IEDMT) is de Spaanse registratiebelasting. Het
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
    aanmerking komt en vragen de vrijstelling voor u aan.
</p>

<h2>Stap 5: inschrijving bij de DGT en Spaanse kentekenplaten</h2>
<p>
    Na de keuring dient de gestoría (het Spaanse administratiekantoor) de aanvraag in bij de DGT, de Spaanse
    tegenhanger van de RDW. U ontvangt een Spaans kentekenbewijs en de definitieve Spaanse kentekenplaten. Daarmee is
    de auto officieel Spaans.
</p>

<h2>Wat kost auto invoeren in Spanje?</h2>
<p>Dit zijn de vaste kosten in Spanje, bovenop onze pakketprijs:</p>
<table>
    <thead>
        <tr><th>Kostenpost</th><th>Bedrag</th></tr>
    </thead>
    <tbody>
        @foreach ($fixedCosts as $cost)
            <tr><td>{{ $cost['label'] }}</td><td>{{ $euro($cost['amount_eur']) }}</td></tr>
        @endforeach
        <tr><td>Registratiebelasting (IEDMT)</td><td>0% tot 14,75%, afhankelijk van CO₂</td></tr>
    </tbody>
</table>
<p>
    <strong>Rekenvoorbeeld.</strong> Een auto met COC, uitstoot onder de 120 g/km en tijdelijke kentekenplaten: de
    Spaanse kosten zijn dan {{ $euro($exampleSpanish) }}. Met het pakket {{ $compleet['name'] }}
    ({{ $euro($compleet['price_eur']) }}) betaalt u in totaal {{ $euro($exampleSpanish + $compleet['price_eur']) }}.
    Daar staat de BPM-teruggave uit Nederland tegenover. Wat dat voor uw auto netto betekent, ziet u met de
    <a href="/#offerte">kentekencheck</a>.
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
