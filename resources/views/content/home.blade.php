{{--
    Long-form homepage copy. Rendered once on the server and used twice:
    as a prop for Welcome.vue (v-html) and inside the crawler fallback HTML.
    The step list and cost overview opt out of .prose (not-prose) so they
    read as scannable blocks; prices come from config via the controller.
--}}
<h2>Auto invoeren in Spanje als Nederlander: wat komt erbij kijken?</h2>
<p>
    Veel Nederlanders wonen of verblijven langdurig aan de Costa del Sol. Op een gegeven moment komt de vraag:
    moet mijn auto op een Spaans kenteken? Voor wie zich in Spanje vestigt is het antwoord ja, en dan begint een
    traject dat veel mensen onderschatten. <strong>Auto invoeren in Spanje is meer dan een sticker vervangen.</strong>
    Het gaat om officiële registratie bij de Spaanse overheid, een technische keuring, registratiebelasting en, aan
    de Nederlandse kant, het uitschrijven bij de RDW en het terugvragen van de resterende BPM.
</p>
<p>
    Wij begeleiden Nederlanders door dit complete traject: van het eerste kenteken opzoeken tot het moment dat u met
    een Spaans kentekenbewijs in uw hand rijdt. Geen verrassingen achteraf en geen wachten op antwoorden in het
    Spaans, maar duidelijke begeleiding in uw eigen taal. Alle stappen, documenten en kosten staan in onze gids
    over <a href="/auto-invoeren-spanje">auto invoeren in Spanje</a>.
</p>

<h2>Wanneer moet uw auto op een Spaans kenteken?</h2>
<p>
    Gaat u in Spanje wonen, dan moet u uw auto op een Spaans kenteken zetten. Wie hier alleen tijdelijk verblijft,
    zoals een overwinteraar, mag onder voorwaarden langer met een Nederlands kenteken rijden. De regels hangen af
    van uw persoonlijke situatie, en de handhaving is de afgelopen jaren strenger geworden. Lees in ons artikel
    <a href="/blog/wanneer-auto-op-spaans-kenteken-verplicht">wanneer uw auto op een Spaans kenteken moet</a> wat
    geldt voor residenten, overwinteraars en niet-residenten.
</p>
<p>
    Ook bij een leaseauto gelden aparte regels rondom toestemming van de leasemaatschappij. Twijfelt u of uw
    situatie hieronder valt? <a href="/contact">Neem contact op</a> voor een vrijblijvend adviesgesprek.
</p>

<h2>De stappen van het importproces op een rij</h2>
<p>
    Het invoeren van een auto in Spanje bestaat uit twee trajecten: één in Nederland en één in Spanje. Beide moeten
    goed op elkaar zijn afgestemd, want de timing bepaalt mede of u recht houdt op BPM-teruggave.
</p>
<ol class="not-prose mt-6 space-y-5">
    @foreach ([
        ['Exportmelding bij de RDW', 'In Nederland wordt de auto bij de RDW afgemeld met de status export. U ontvangt een exportkenteken dat 14&nbsp;dagen geldig is om de auto naar Spanje te rijden. Zo werkt <a href="/blog/auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het" class="font-medium text-primary underline underline-offset-4">de auto uitschrijven bij de RDW</a>.'],
        ['Rest-BPM terugvragen', 'Op basis van de exportmelding vraagt u bij de Belastingdienst de <strong>rest-BPM</strong> terug: het deel van de oorspronkelijke belasting dat nog in de auto zit. Hoeveel dat is, hangt af van de leeftijd van de auto en de BPM die bij registratie in Nederland is betaald. Een voorbeeld: een auto van vijf jaar met €&nbsp;6.000 oorspronkelijke BPM levert ongeveer €&nbsp;1.800 op. Bereken het met de <a href="/bpm-calculator" class="font-medium text-primary underline underline-offset-4">BPM-calculator</a>.'],
        ['ITV-keuring in Spanje', 'In Spanje doorloopt de auto de <strong>ITV-keuring</strong>, de Spaanse APK, bij een erkend keuringsstation. Lees hoe u uw <a href="/blog/itv-keuring-auto-import-spanje" class="font-medium text-primary underline underline-offset-4">Nederlandse auto in Spanje laat keuren</a>.'],
        ['Registratiebelasting of vrijstelling', 'Daarna wordt de registratiebelasting (<em>Impuesto Especial sobre Determinados Medios de Transporte</em>, IEDMT) berekend op basis van de CO₂-uitstoot, of vrijgesteld als u uw hoofdverblijf naar Spanje verhuist. Meer over <a href="/blog/registratiebelasting-iedmt-spanje-vrijstelling-verhuizing" class="font-medium text-primary underline underline-offset-4">de IEDMT en de vrijstelling</a>.'],
        ['Spaans kentekenbewijs', 'Na goedkeuring van alle documenten ontvangt u het definitieve Spaanse kentekenbewijs en de platen via de DGT. Bekijk onze <a href="/tarieven" class="font-medium text-primary underline underline-offset-4">tarieven- en kostenpagina</a> voor een compleet overzicht van alle te verwachten kosten.'],
    ] as [$title, $body])
        <li class="flex gap-4">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary font-display text-sm font-semibold text-primary-foreground" aria-hidden="true">{{ $loop->iteration }}</span>
            <div>
                <h3 class="font-display text-lg font-semibold text-foreground">{{ $title }}</h3>
                <p class="mt-1 text-base leading-relaxed text-foreground">{!! $body !!}</p>
            </div>
        </li>
    @endforeach
</ol>

<h2>Hoeveel kost het om een auto in Spanje te registreren?</h2>
<p>
    De totale kosten bestaan uit vaste en variabele onderdelen. De <strong>vaste kosten</strong> zijn onder meer de
    leges bij de DGT, de nummerplaten, de gestoría en de ITV-keuring, en zonder COC de Ficha Técnica. De variabele
    kosten zijn de registratiebelasting (IEDMT), die afhangt van de CO₂-uitstoot: auto's tot 120&nbsp;g/km betalen
    niets, daarboven loopt het tarief op van 4,75% tot 14,75% van de waarde.
</p>
<dl class="not-prose mt-6 grid gap-px overflow-hidden rounded-xl border border-border bg-border sm:grid-cols-3">
    <div class="bg-card p-5">
        <dt class="text-sm text-muted-foreground">Onze begeleiding</dt>
        <dd class="mt-1 font-display text-2xl font-semibold text-foreground">vanaf €&nbsp;{{ number_format($lowestPackagePrice, 0, ',', '.') }}</dd>
        <dd class="mt-1 text-sm text-muted-foreground">Vaste prijs, vooraf bekend</dd>
    </div>
    <div class="bg-card p-5">
        <dt class="text-sm text-muted-foreground">Vaste kosten in Spanje</dt>
        <dd class="mt-1 font-display text-2xl font-semibold text-foreground">ca. €&nbsp;{{ number_format(round($fixedCostsTotal, -1), 0, ',', '.') }}</dd>
        <dd class="mt-1 text-sm text-muted-foreground">Leges, platen, gestoría, ITV</dd>
    </div>
    <div class="bg-card p-5">
        <dt class="text-sm text-muted-foreground">Registratiebelasting</dt>
        <dd class="mt-1 font-display text-2xl font-semibold text-foreground">0–14,75%</dd>
        <dd class="mt-1 text-sm text-muted-foreground">Op basis van CO₂, of vrijgesteld bij verhuizing</dd>
    </div>
</dl>
<p>
    Met de kentekencheck bovenaan deze pagina ziet u direct wat het hele traject voor uw auto netto kost, met de
    BPM-teruggave al verrekend. Wilt u alleen de BPM weten? Gebruik dan de <a href="/bpm-calculator">BPM-calculator</a>.
    Alle pakketten en kosten staan op de <a href="/tarieven">tarievenpagina</a>.
</p>

<h2>Waarom kiezen voor een specialist?</h2>
<p>
    Het importproces lijkt op papier overzichtelijk, maar in de praktijk gaat er veel mis: documenten die niet
    kloppen, een ITV die wordt afgekeurd, een registratiebelasting die hoger uitvalt dan verwacht, of een BPM-aanvraag
    die te laat wordt ingediend omdat de Spaanse inschrijving uitloopt.
    <strong>Eén gemiste deadline en de BPM-teruggave kan vervallen.</strong>
</p>
<p>
    Maikel kent de Spaanse kant van het traject, van de ITV tot het definitieve kentekenbewijs, en is uw vaste
    aanspreekpunt. Sebastiaan houdt de berekening en uw dossier online bij. Samen zorgen we dat beide trajecten op
    elkaar zijn afgestemd en dat u nergens op hoeft te wachten.
</p>
<p>
    Bekijk onze <a href="/diensten">dienstenpagina</a> voor een volledig overzicht van wat inbegrepen is, of lees op
    onze <a href="/over-ons">Over ons-pagina</a> meer over wie wij zijn en hoe we werken.
</p>
