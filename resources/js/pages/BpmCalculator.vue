<script setup lang="ts">
import KentekenLookup from '@/components/KentekenLookup.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <PublicLayout
        title="BPM-calculator — Bereken uw BPM-teruggave"
        description="Voer uw kenteken in en zie direct hoeveel BPM-teruggave u kunt verwachten bij export van uw auto naar Spanje."
    >
        <section class="bg-background">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24">
                <span class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground">
                    Gratis berekening
                </span>
                <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-5xl">
                    Hoeveel <span class="text-accent">BPM</span> krijgt u terug?
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-muted-foreground">
                    Voer uw kenteken in en bereken direct de indicatieve BPM-teruggave bij export naar Spanje. De berekening gebruikt de forfaitaire afschrijvingstabel op basis van de RDW-gegevens.
                </p>

                <div class="mx-auto mt-10 max-w-3xl text-left">
                    <KentekenLookup :bpm-only="true" />
                </div>
            </div>
        </section>

        <!-- SEO content -->
        <section class="bg-secondary/30 border-t border-border">
            <div class="container mx-auto max-w-3xl px-4 py-16 md:py-20">
                <div class="prose prose-lg max-w-none prose-headings:font-display prose-headings:text-foreground prose-h2:mt-12 prose-h2:text-2xl prose-h3:mt-8 prose-h3:text-xl prose-p:text-foreground prose-strong:text-foreground prose-a:text-accent prose-a:font-medium hover:prose-a:underline">

                    <h2>Wat is BPM en waarom krijgt u geld terug bij export?</h2>
                    <p>
                        <strong>BPM staat voor Belasting van personenauto's en motorrijwielen.</strong> Dit is een Nederlandse belasting die wordt geheven op het moment dat een auto voor het eerst in Nederland op kenteken wordt gezet. Afhankelijk van het model, de brandstofsoort en de CO₂-uitstoot kan dit bedrag oplopen van enkele honderden tot tienduizenden euro's. Dat geld is inbegrepen in de aanschafprijs van elke nieuwe — en tweedehands — auto die in Nederland rijdt.
                    </p>
                    <p>
                        Het principe achter de <strong>BPM-teruggave bij export</strong> is simpel: wanneer een auto Nederland permanent verlaat en in een ander EU-land wordt geregistreerd, is er geen reden meer voor Nederland om de volledige BPM te houden. Een deel van die belasting zit immers nog in de auto — het gedeelte dat overeenkomt met de resterende waarde van het voertuig. Exporteert u uw auto naar Spanje en schrijft u hem uit bij de RDW, dan kunt u dit resterende bedrag terugvragen bij de Belastingdienst. <strong>Wie dit oversloeg, liet soms duizenden euro's liggen.</strong>
                    </p>

                    <h2>Hoe werkt de BPM-berekening in deze calculator?</h2>
                    <p>
                        Onze BPM-calculator gebruikt de <strong>forfaitaire afschrijvingstabel</strong> die de Belastingdienst hanteert. U voert uw kenteken in, waarna wij via de RDW de relevante voertuiggegevens ophalen: de datum van eerste toelating, de brandstofsoort en de catalogusprijs. Op basis van de leeftijd van de auto en de officiële afschrijvingstabel berekenen wij de indicatieve rest-BPM.
                    </p>
                    <p>
                        Let op: dit is een <strong>indicatieve berekening</strong>. De werkelijke teruggave kan hoger uitvallen wanneer de auto sterker is afgeschreven dan de tabel aangeeft — bijvoorbeeld door een hoge kilometerstand of zichtbare gebruikssporen. In dat geval kan een onafhankelijk taxatierapport van een erkend taxateur tot een hogere uitkering leiden. Neem bij twijfel <a href="/contact">contact met ons op</a> voor een vrijblijvend adviesgesprek.
                    </p>

                    <h2>Wanneer heeft u recht op BPM-teruggave?</h2>
                    <p>
                        De Belastingdienst hanteert een aantal strikte voorwaarden waaraan u <strong>allemaal</strong> moet voldoen:
                    </p>
                    <ul>
                        <li><strong>Eerste Nederlandse tenaamstelling op of na 16 oktober 2006.</strong> Auto's die vóór die datum voor het eerst in Nederland zijn geregistreerd, vallen buiten de regeling.</li>
                        <li><strong>De auto is geen schadevoertuig.</strong> Een auto met de RDW-status 'schade' komt niet in aanmerking.</li>
                        <li><strong>U meldt de auto af voor export bij de RDW.</strong> De officiële exportmelding is de starthandeling voor de teruggaveprocedure.</li>
                        <li><strong>De auto wordt binnen 13 weken na de exportmelding in Spanje geregistreerd.</strong> Deze termijn is strikt; te laat betekent geen teruggave.</li>
                        <li><strong>U levert bewijs van de Spaanse registratie</strong> aan de Belastingdienst. Zonder Spaans kentekenbewijs wordt de aanvraag afgewezen.</li>
                    </ul>
                    <p>
                        Wij begeleiden u door dit hele traject — van de RDW-exportmelding in Nederland tot en met de definitieve Spaanse inschrijving. Bekijk onze <a href="/diensten">dienstenpagina</a> voor een volledig overzicht van wat wij voor u regelen.
                    </p>

                    <h2>Hoe hoog is de BPM-teruggave bij een auto van vijf jaar oud?</h2>
                    <p>
                        De hoogte van de teruggave hangt af van drie factoren: de <strong>oorspronkelijke BPM-waarde</strong> bij eerste registratie, de <strong>leeftijd van de auto</strong> en de <strong>afschrijvingsmethode</strong>. Ter indicatie: bij een vijf jaar oude middenklasse benzineauto met een oorspronkelijke BPM van €6.000 kunt u bij de forfaitaire methode rekenen op een teruggave van ruwweg €1.500 tot €2.500, afhankelijk van het exacte bouwjaar en de maand van exportmelding.
                    </p>
                    <p>
                        <strong>Hoe jonger de auto, hoe meer u terugkrijgt.</strong> Dat klinkt logisch, maar de praktijk laat zien dat veel mensen de BPM-teruggave pas overwegen nádat ze al maanden bezig zijn met de Spaanse kant van het traject. Elke maand uitstel verlaagt het terug te ontvangen bedrag. Het loont dus om dit vroeg in het proces te regelen.
                    </p>

                    <h2>BPM-teruggave en de kosten van auto invoeren in Spanje</h2>
                    <p>
                        De BPM-teruggave staat volledig los van de <strong>Spaanse invoerbelasting</strong> — de <em>Impuesto de Matriculación</em> of IEDMT. Dat zijn twee aparte processen in twee landen. De BPM-teruggave verlaagt uw netto-kosten aan de Nederlandse kant; de Spaanse belasting is een kostenpost aan de Spaanse kant. Door beide goed te timen en te combineren, kunt u het financiële plaatje aanzienlijk verbeteren.
                    </p>
                    <p>
                        Op onze <a href="/tarieven">tarieven- en kostenpagina</a> vindt u een volledig overzicht van alle kosten die komen kijken bij het importeren van een auto naar Spanje: van de ITV-keuring en het aanvragen van een definitief kenteken tot de notariskosten en onze servicekosten. Zo weet u vooraf exact waar u aan toe bent — zonder verrassingen achteraf.
                    </p>

                    <h2>Zelf regelen of uitbesteden?</h2>
                    <p>
                        De BPM-teruggaveprocedure kunt u in theorie zelf doorlopen. U heeft daarvoor het juiste formulier van de Belastingdienst nodig, de originele exportdocumenten van de RDW en het Spaanse inschrijvingsbewijs — en dat alles binnen de termijn van 13 weken. In de praktijk lopen veel mensen vast op de timing: de Spaanse registratie duurt langer dan verwacht, of er ontbreekt een document waardoor de aanvraag wordt afgewezen.
                    </p>
                    <p>
                        <strong>Wij regelen het volledige traject voor u</strong> — inclusief de BPM-teruggaveaanvraag. Zo hoeft u zelf niets bij te houden en loopt u geen geld mis door een gemiste deadline. Bekijk onze <a href="/diensten">diensten</a> of lees meer over wat ons <a href="/tarieven">Compleet-pakket</a> inhoudt. Heeft u een specifieke vraag? <a href="/contact">Neem gerust contact op</a> — wij denken graag vrijblijvend met u mee.
                    </p>

                    <h2>Veelgestelde vragen over BPM-teruggave</h2>
                    <h3>Kan ik BPM terugvragen als ik de auto al heb uitgeschreven?</h3>
                    <p>
                        Ja, zolang de Spaanse registratie binnen 13 weken na de exportmelding bij de RDW heeft plaatsgevonden en u over de juiste documenten beschikt, kunt u de aanvraag nog indienen. De Belastingdienst heeft vervolgens 8 weken om te reageren.
                    </p>
                    <h3>Geldt de BPM-teruggave ook voor elektrische auto's?</h3>
                    <p>
                        Elektrische auto's betaalden tot voor kort geen of nauwelijks BPM, waardoor de teruggave bij export doorgaans zeer beperkt is. Vanaf 2025 betalen EV's wel degelijk BPM, dus voor nieuwere elektrische auto's kan dit in de toekomst veranderen. Gebruik onze calculator om de situatie voor uw specifieke kenteken te bekijken.
                    </p>
                    <h3>Wat is het verschil tussen forfaitaire berekening en een taxatierapport?</h3>
                    <p>
                        De forfaitaire methode is de standaardmethode op basis van leeftijd en cataloguswaarde. Een taxatierapport van een erkend taxateur kan worden ingediend als de werkelijke waardevermindering hoger ligt dan de tabel aangeeft — dit leidt dan tot een hogere teruggave. Wij adviseren u graag welke methode in uw geval voordeliger uitpakt.
                    </p>

                    <p class="mt-10 rounded-xl border border-border bg-card p-5 text-base text-muted-foreground">
                        Meer weten over het volledige importproces? Lees onze <a href="/blog">blogreeks over auto invoeren in Spanje</a> — van het exportkenteken aanvragen tot de definitieve Spaanse inschrijving.
                    </p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
