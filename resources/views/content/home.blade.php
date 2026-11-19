{{--
    Long-form homepage copy. Rendered once on the server and used twice:
    as a prop for Welcome.vue (v-html) and inside the crawler fallback HTML.
    The service-process list and cost overview opt out of .prose (not-prose)
    so they read as scannable blocks; the official step-by-step lives on the
    guide (/auto-op-spaans-kenteken-zetten), not here; prices come from config via the controller.
    The fixed-cost total is computed here so optional items (temporary
    plates) are left out, matching the calculator.
--}}
@php
    $includedFixedCostsTotal = \App\Services\SpainImport\SpainFixedCosts::includedTotal();
    $fichaTecnicaAmount = (float) (collect(config('spain_import.fixed_costs'))->firstWhere('key', 'ficha_tecnica')['amount_eur'] ?? 0);
    $fixedCostsWithCocTotal = $includedFixedCostsTotal - $fichaTecnicaAmount;
@endphp
<h2>Auto invoeren in Spanje als Nederlander: wat komt erbij kijken?</h2>
<p>
    Veel Nederlanders wonen of verblijven langdurig aan de Costa del Sol. Op een gegeven moment komt de vraag:
    moet mijn auto op een Spaans kenteken? Voor wie zich in Spanje vestigt is het antwoord ja, en dan begint een
    traject dat veel mensen onderschatten: officiële registratie bij de Spaanse overheid, een technische keuring,
    registratiebelasting en, aan de Nederlandse kant, het uitschrijven bij de RDW en het terugvragen van de resterende
    BPM.
</p>
<p>
    Wij begeleiden Nederlanders bij het invoeren van hun auto in Spanje: van het eerste kenteken opzoeken tot het
    moment dat u met een Spaans kentekenbewijs in uw hand rijdt. U weet vooraf wat het kost en krijgt uw antwoorden
    in het Nederlands, ook over de Spaanse kant.
</p>

<h2>Wanneer moet uw auto op een Spaans kenteken?</h2>
<p>
    Gaat u in Spanje wonen, dan moet uw auto op een Spaans kenteken. Dat geldt zodra u fiscaal resident bent: u
    verblijft meer dan 183 dagen per jaar in Spanje. Rijden als resident op een buitenlands kenteken kan leiden tot
    een boete, en de handhaving is de afgelopen jaren strenger geworden.
</p>
<p>
    Bij <a href="/blog/leaseauto-of-gefinancierde-auto-meenemen-naar-spanje">een leaseauto of gefinancierde auto</a>
    heeft u daarnaast schriftelijke toestemming van de leasemaatschappij of financier nodig. Twijfelt u of uw
    situatie hieronder valt? <a href="/contact">Neem contact op</a> voor een vrijblijvend adviesgesprek.
</p>

<h2>Auto invoeren in Spanje als niet-resident of overwinteraar</h2>
<p>
    Woont u in Nederland en verblijft u minder dan 183 dagen per jaar in Spanje, dan mag u met uw Nederlandse auto
    blijven rijden en hoeft u hem niet in te schrijven. Heeft u een huis aan de Costa del Sol en bent u geen
    resident, dan kunt u de auto in Spanje laten inschrijven, al is dat voor u niet verplicht. U betaalt dan de registratiebelasting
    volgens het gewone CO₂-tarief, want de vrijstelling geldt alleen bij een verhuizing van uw hoofdverblijf. Lees in
    ons artikel <a href="/blog/wanneer-auto-op-spaans-kenteken-verplicht">wanneer uw auto op een Spaans kenteken
    moet</a> wat geldt voor residenten, overwinteraars en niet-residenten.
</p>

<h2>Zo werkt auto invoeren in Spanje met ons</h2>
<p>
    Hoeveel wij voor u regelen, hangt af van uw pakket. Bij Basis houdt u zelf de regie over de export en de ITV, bij
    Compleet en Compleet Plus nemen wij die stappen over.
</p>
<ol class="not-prose mt-6 space-y-5">
    @foreach ([
        ['Offerte op basis van uw kenteken', 'U vult uw Nederlandse kenteken in de kostencheck in en ziet de BPM-teruggave, de vaste kosten in Spanje en een indicatie van de registratiebelasting. Daarna kiest u een pakket en vraagt u een offerte aan.'],
        ['Documenten uploaden in het klantenportaal', 'Na uw akkoord zet u uw documenten in uw persoonlijke klantenportaal, zoals uw paspoort, NIE-nummer, kentekenbewijs en het COC-document. Wij controleren of het dossier compleet is. Bij Compleet Plus komen wij bij u thuis voor de foto\'s en documenten.'],
        ['Export en BPM-teruggave', 'Bij Compleet en Compleet Plus loopt de export via een van onze erkende RDW-exportpartners, inclusief de aanvraag van de BPM-teruggave. Bij Basis regelt u de export en de BPM-teruggave zelf. Zo werkt <a href="/blog/auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het" class="font-medium text-primary underline underline-offset-4">de auto uitschrijven bij de RDW</a>.'],
        ['ITV-keuring', 'Bij Compleet plannen wij de afspraak bij een erkend keuringsstation, bij Compleet Plus gaan wij namens u naar de keuring. Lees hoe <a href="/blog/itv-keuring-auto-import-spanje" class="font-medium text-primary underline underline-offset-4">de ITV-keuring bij import</a> verloopt.'],
        ['DGT en Spaans kenteken', 'Wij verwerken de aanvraag bij de DGT, berekenen de registratiebelasting of vragen <a href="/blog/registratiebelasting-iedmt-spanje-vrijstelling-verhuizing" class="font-medium text-primary underline underline-offset-4">de vrijstelling bij verhuizing</a> aan, en leveren het Spaanse kentekenbewijs en de platen.'],
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
<p>
    Reken op gemiddeld vier tot acht weken van de exportmelding tot het Spaanse kentekenbewijs. In het klantenportaal
    ziet u steeds hoe het met uw auto staat. Wat er bij elke officiële stap gebeurt, van de RDW tot de DGT, leest u in
    ons <a href="/auto-op-spaans-kenteken-zetten">stappenplan: Nederlandse auto op Spaans kenteken zetten</a>. Wat elk
    onderdeel van onze begeleiding inhoudt, staat op de <a href="/diensten">dienstenpagina</a>.
</p>

<h2>Hoeveel kost het om een auto in Spanje te registreren?</h2>
<p>
    De totale kosten bestaan uit vaste en variabele onderdelen. De vaste kosten zijn onder meer de
    leges bij de DGT, de nummerplaten, de gestoría en de ITV-keuring, en zonder COC de Ficha Técnica. De variabele
    kosten zijn de registratiebelasting (IEDMT), die afhangt van de CO₂-uitstoot: auto's tot 120&nbsp;g/km betalen
    niets, daarboven loopt het tarief op van 4,75% tot 14,75% van de waarde. Drie uitgewerkte voorbeelden vindt u in
    ons artikel over <a href="/blog/kosten-auto-invoeren-spanje-rekenvoorbeelden">de kosten van auto invoeren in Spanje</a>.
</p>
<dl class="not-prose mt-6 grid gap-px overflow-hidden rounded-xl border border-border bg-border sm:grid-cols-3">
    <div class="bg-card p-5">
        <dt class="text-sm text-muted-foreground">Onze begeleiding</dt>
        <dd class="mt-1 font-display text-2xl font-semibold text-foreground">vanaf €&nbsp;{{ number_format($lowestPackagePrice, 0, ',', '.') }}</dd>
        <dd class="mt-1 text-sm text-muted-foreground">Vaste prijs, vooraf bekend</dd>
    </div>
    <div class="bg-card p-5">
        <dt class="text-sm text-muted-foreground">Vaste kosten in Spanje</dt>
        <dd class="mt-1 font-display text-2xl font-semibold text-foreground">ca. €&nbsp;{{ number_format(round($includedFixedCostsTotal, -1), 0, ',', '.') }}</dd>
        <dd class="mt-1 text-sm text-muted-foreground">Leges, gestoría, ITV en Ficha Técnica, zonder COC. Met COC ca. €&nbsp;{{ number_format(round($fixedCostsWithCocTotal, -1), 0, ',', '.') }}. Excl. optionele tijdelijke platen</dd>
    </div>
    <div class="bg-card p-5">
        <dt class="text-sm text-muted-foreground">Registratiebelasting</dt>
        <dd class="mt-1 font-display text-2xl font-semibold text-foreground">0–14,75%</dd>
        <dd class="mt-1 text-sm text-muted-foreground">Op basis van CO₂, of vrijgesteld bij verhuizing</dd>
    </div>
</dl>
<p>
    Met de kostencheck bovenaan deze pagina ziet u direct wat het hele traject voor uw auto netto kost, met de
    BPM-teruggave al verrekend. Wilt u alleen de BPM weten? Gebruik dan de <a href="/bpm-calculator">BPM-calculator</a>.
    Alle pakketten en kosten staan op de <a href="/tarieven">tarievenpagina</a>.
</p>

<h2>Waarom kiezen voor een specialist?</h2>
<p>
    Het importproces lijkt op papier overzichtelijk, maar in de praktijk gaat er veel mis: documenten die niet
    kloppen, een ITV die wordt afgekeurd, een registratiebelasting die hoger uitvalt dan verwacht, of een Spaanse inschrijving
    die uitloopt. Staat de auto niet binnen 13 weken na de exportmelding op een Spaans kenteken, dan vervalt de
    BPM-teruggave.
</p>
<p>
    Maikel kent de Spaanse kant van het traject, van de ITV tot het definitieve kentekenbewijs, en is uw vaste
    aanspreekpunt. Sebastiaan houdt de berekening en uw dossier online bij. Samen stemmen we de Nederlandse en de
    Spaanse kant op elkaar af.
</p>
<p>
    Vragen over documenten, doorlooptijd of de BPM-teruggave beantwoorden we bij de
    <a href="/veelgestelde-vragen">veelgestelde vragen over auto invoeren in Spanje</a>. Op onze
    <a href="/over-ons">Over ons-pagina</a> leest u meer over wie wij zijn en hoe we werken.
</p>
