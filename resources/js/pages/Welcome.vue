<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import KentekenLookup from '@/components/KentekenLookup.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

withDefaults(
    defineProps<{
        canRegister?: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <PublicLayout
        title="Auto op Spaans kenteken — voor Nederlanders in Spanje"
        description="Wij regelen het hele traject om je Nederlandse auto op Spaans kenteken te zetten — van papierwerk en BPM-teruggave tot de ITV. Voor Nederlanders aan de Costa del Sol."
    >
        <section class="relative overflow-hidden bg-background">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24">
                <span
                    class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground"
                >
                    Voor Nederlanders aan de Costa del Sol
                </span>
                <h1
                    class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-6xl"
                >
                    Je auto op
                    <span class="text-accent">Spaans kenteken</span>,
                    zonder gedoe.
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-muted-foreground">
                    Van papierwerk tot ITV en BPM-teruggave — wij regelen het
                    complete traject. Voer hieronder je kenteken in en zie direct
                    wat je auto je oplevert.
                </p>

                <div class="mx-auto mt-10 max-w-3xl text-left">
                    <KentekenLookup />
                </div>

                <div
                    class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-muted-foreground"
                >
                    <span class="inline-flex items-center gap-2">
                        <Check class="size-4 stroke-[3] text-success" />
                        Vaste prijs vooraf
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <Check class="size-4 stroke-[3] text-success" />
                        Nederlandstalige begeleiding
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <Check class="size-4 stroke-[3] text-success" />
                        BPM-teruggave inbegrepen
                    </span>
                </div>
            </div>
        </section>

        <!-- SEO content -->
        <section class="border-t border-border bg-secondary/30">
            <div class="container mx-auto max-w-3xl px-4 py-16 md:py-20">
                <div class="prose prose-lg max-w-none prose-headings:font-display prose-headings:text-foreground prose-h2:mt-12 prose-h2:text-2xl prose-h3:mt-8 prose-h3:text-xl prose-p:text-foreground prose-strong:text-foreground prose-a:text-accent prose-a:font-medium hover:prose-a:underline">

                    <h2>Auto invoeren in Spanje als Nederlander — wat komt erbij kijken?</h2>
                    <p>
                        Steeds meer Nederlanders wonen of verblijven langdurig aan de Costa del Sol, in Madrid of elders in Spanje. Op een gegeven moment rijst de vraag: moet mijn auto op een Spaans kenteken? Het antwoord is in de meeste gevallen ja — en dan begint een traject dat veel mensen onderschatten. <strong>Auto invoeren in Spanje is meer dan een sticker vervangen.</strong> Het gaat om officiële registratie bij de Spaanse overheid, een technische keuring, invoerbelasting en — aan de Nederlandse kant — het uitschrijven bij de RDW en het terugvragen van de resterende BPM.
                    </p>
                    <p>
                        Wij begeleiden Nederlanders door dit complete traject. Van het eerste kenteken opzoeken tot het moment dat u met een Spaans kentekenbewijs in uw hand rijdt. Geen verrassingen achteraf, geen wachten op antwoorden in het Spaans — gewoon duidelijke begeleiding in uw eigen taal.
                    </p>

                    <h2>Wanneer bent u verplicht uw auto in Spanje te registreren?</h2>
                    <p>
                        Spanje hanteert een duidelijke regel: <strong>woont of verblijft u langer dan 183 dagen per jaar in Spanje, dan bent u fiscaal resident en bent u verplicht uw auto op een Spaans kenteken te zetten.</strong> Rijdt u langer dan 30 aaneengesloten dagen in Spanje met een buitenlands kenteken, dan kunt u al worden aangehouden. De handhaving hierop is de afgelopen jaren aanzienlijk strenger geworden, met boetes die kunnen oplopen tot duizenden euro's.
                    </p>
                    <p>
                        Ook als u de auto in Nederland verkoopt en in Spanje een andere auto koopt, loopt u hier tegenaan. En bij een leaseauto gelden aparte regels rondom toestemming van de leasemaatschappij. Twijfelt u of uw situatie hieronder valt? <a href="/contact">Neem contact op</a> voor een vrijblijvend adviesgesprek.
                    </p>

                    <h2>De stappen van het importproces op een rij</h2>
                    <p>
                        Het invoeren van een auto in Spanje bestaat uit twee parallelle trajecten: één in Nederland en één in Spanje. Beide moeten goed op elkaar zijn afgestemd, want de timing bepaalt mede of u recht heeft op BPM-teruggave.
                    </p>
                    <h3>Het Nederlandse deel</h3>
                    <p>
                        In Nederland meldt u de auto af bij de RDW met de status 'Export'. U ontvangt een exportkenteken dat 14 dagen geldig is om de auto naar Spanje te rijden. Tegelijkertijd kunt u — op basis van de exportmelding — een aanvraag indienen bij de Belastingdienst voor de <strong>rest-BPM</strong>: het deel van de oorspronkelijke belasting dat nog in de auto zit. Voor een gemiddelde auto kan dit bedrag oplopen van €500 tot ruim €3.000, afhankelijk van het merk, bouwjaar en de oorspronkelijke catalogusprijs.
                    </p>
                    <h3>Het Spaanse deel</h3>
                    <p>
                        In Spanje doorloopt de auto de <strong>ITV-keuring</strong> — de Spaanse APK — bij een erkend keuringsstation. Vervolgens wordt de <em>Impuesto de Matriculación</em> (IEDMT) berekend en betaald: de Spaanse invoerbelasting op basis van de CO₂-uitstoot van uw auto. Na betaling en goedkeuring van alle documenten ontvangt u het definitieve Spaanse kentekenbewijs. Bekijk onze <a href="/tarieven">tarieven- en kostenpagina</a> voor een compleet overzicht van alle te verwachten kosten.
                    </p>

                    <h2>Hoeveel kost het om een auto in Spanje te registreren?</h2>
                    <p>
                        De totale kosten bestaan uit vaste en variabele onderdelen. De <strong>vaste kosten</strong> zijn onder meer de ITV-keuring, de aanvraag van het Spaanse kentekenbewijs (ficha técnica), de nummerplaten en de leges voor de definitieve inschrijving. De <strong>variabele kosten</strong> zijn de Spaanse invoerbelasting (IEDMT), die afhankelijk is van de CO₂-uitstoot: schone auto's betalen weinig of niets, terwijl een auto met een hoge uitstoot aanzienlijk meer belasting kost.
                    </p>
                    <p>
                        Onze <a href="/bpm-calculator">BPM-calculator</a> geeft u direct inzicht in de indicatieve BPM-teruggave die u in Nederland kunt verwachten. Zo ziet u in één oogopslag wat het importtraject u per saldo kost — of oplevert.
                    </p>

                    <h2>Waarom kiezen voor een specialist?</h2>
                    <p>
                        Het importproces lijkt op papier overzichtelijk, maar in de praktijk gaat er veel mis. Documenten die niet kloppen, een ITV die wordt afgekeurd, een IEDMT-berekening die hoger uitvalt dan verwacht, of een BPM-aanvraag die te laat wordt ingediend omdat de Spaanse inschrijving uitloopt. <strong>Eén gemiste deadline en de BPM-teruggave vervalt volledig.</strong>
                    </p>
                    <p>
                        Wij kennen het proces van binnen en buiten. Maikel is specialist in de Spaanse kant van het traject — van de ITV tot het definitieve kentekenbewijs — en regelt al het klantencontact en de afhandeling. Samen zorgen we dat beide trajecten op elkaar zijn afgestemd en dat u nergens voor staat te wachten.
                    </p>
                    <p>
                        Bekijk onze <a href="/diensten">dienstenpagina</a> voor een volledig overzicht van wat inbegrepen is, of lees op onze <a href="/over-ons">Over ons-pagina</a> meer over wie wij zijn en hoe we werken.
                    </p>

                    <h2>Veelgestelde vragen over auto invoeren in Spanje</h2>
                    <h3>Kan ik mijn auto ook tijdelijk op Nederlands kenteken in Spanje houden?</h3>
                    <p>
                        Als toerist of bij kort verblijf is dat toegestaan. Zodra u fiscaal resident bent in Spanje (meer dan 183 dagen per jaar), bent u verplicht de auto in te schrijven. Tijdelijk rijden op een buitenlands kenteken als resident is niet toegestaan en kan leiden tot hoge boetes en inbeslagname van het voertuig.
                    </p>
                    <h3>Wat als mijn auto nog op naam van een Nederlandse leasemaatschappij staat?</h3>
                    <p>
                        In dat geval heeft u schriftelijke toestemming nodig van de leasemaatschappij om de auto naar het buitenland te brengen en in te schrijven. Veel leasemaatschappijen werken hier aan mee, maar de voorwaarden variëren. Wij adviseren u graag over de juiste aanpak voor uw specifieke situatie.
                    </p>
                    <h3>Hoe lang duurt het hele traject?</h3>
                    <p>
                        Reken op gemiddeld vier tot acht weken vanaf de exportmelding bij de RDW tot het ontvangen van het definitieve Spaanse kentekenbewijs. De ITV en de administratieve verwerking in Spanje bepalen grotendeels de doorlooptijd. Wij houden u gedurende het hele traject op de hoogte via uw persoonlijk <a href="/contact">klantenportaal</a>.
                    </p>

                    <p class="mt-10 rounded-xl border border-border bg-card p-5 text-base text-muted-foreground">
                        Wilt u meer lezen over het importproces? In onze <a href="/blog">blogreeks</a> behandelen we onderwerpen als BPM-teruggave, het exportkenteken aanvragen, de ITV-keuring en de Spaanse invoerbelasting — stap voor stap uitgelegd.
                    </p>

                </div>
            </div>
        </section>
    </PublicLayout>
</template>
