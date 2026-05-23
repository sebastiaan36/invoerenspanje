<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Car,
    ClipboardCheck,
    ShieldCheck,
    Euro,
    Truck,
    CheckCircle,
} from 'lucide-vue-next';
import PublicLayout from '@/layouts/PublicLayout.vue';

const services = [
    {
        icon: Car,
        slug: 'auto-op-spaans-kenteken',
        title: 'Auto op Spaans kenteken',
        tagline: 'Van Nederlandse naar Spaanse registratie — wij regelen het complete traject.',
        description:
            'Als u langer dan zes maanden per jaar in Spanje verblijft, bent u wettelijk verplicht uw auto op Spaans kenteken te zetten. Wij begeleiden het volledige traject: van het verzamelen van de benodigde documenten tot het aanvragen van de matriculación bij de Dirección General de Tráfico (DGT). U heeft geen Spaans nodig en hoeft nergens in de rij te staan.',
        includes: [
            'Volledige aanvraag bij de DGT namens u',
            'Ficha Técnica of COC-verwerking',
            'Groene matrículas temporales (tijdelijke platen)',
            'Definitieve Spaanse kentekenplaten',
            'Communicatie met overheidsinstanties in het Spaans',
            'Begeleiding bij aanvullende documenten (NIE, empadronamiento)',
        ],
        cta: { label: 'Bereken uw kosten', href: '/' },
        accent: true,
    },
    {
        icon: Euro,
        slug: 'bpm-teruggave',
        title: 'BPM-teruggave',
        tagline: 'Haal terug wat u bij aankoop betaalde — tot duizenden euro\'s.',
        description:
            'Bij de aankoop van uw auto in Nederland betaalde u BPM (Belasting van Personenauto\'s en Motorrijwielen). Exporteert u uw auto naar Spanje, dan heeft u recht op teruggave van het resterende BPM-bedrag. De hoogte hangt af van de leeftijd van uw auto en de originele catalogusprijs. Wij dienen de teruggaveaanvraag in bij de Nederlandse Belastingdienst.',
        includes: [
            'Berekening via de forfaitaire afschrijvingstabel',
            'Vergelijking met koerslijst- en taxatiemethode',
            'Aanvraag bij de Nederlandse Belastingdienst',
            'Verwerking exportverklaring (RDW)',
            'Uitbetaling direct op uw rekening',
        ],
        cta: { label: 'Bereken uw BPM-teruggave', href: '/bpm-calculator' },
        accent: false,
    },
    {
        icon: Car,
        slug: 'auto-export-nederland',
        title: 'Auto-export Nederland',
        tagline: 'Correcte uitschrijving bij de RDW — vereist voor elke import in Spanje.',
        description:
            'Voordat uw auto in Spanje geregistreerd kan worden, moet hij officieel worden uitgeschreven uit het Nederlandse kentekenregister. Dit heet de exportverklaring. Wij regelen dit via een van onze gecertificeerde RDW-exportpartners in Nederland. U hoeft hier niet zelf voor naar een RDW-kantoor.',
        includes: [
            'Exportverklaring via gecertificeerde RDW-partner',
            'Ophalen of inleveren via partnernetwerk',
            'Teruggave van de Nederlandse kentekenplaten',
            'Exportdocumentatie voor de Spaanse DGT',
            'Coördinatie met ons Spaanse kantoor',
        ],
        cta: { label: 'Bekijk pakketten', href: '/tarieven' },
        accent: false,
    },
    {
        icon: Truck,
        slug: 'transport',
        title: 'Transport Nederland → Spanje',
        tagline: 'Wij regelen het vervoer van uw auto — u hoeft er niet zelf mee te rijden.',
        description:
            'Na de export in Nederland kunt u uw auto laten transporteren naar uw adres in Spanje. Wij coördineren het transport via ons partnernetwerk van gecertificeerde autotransporteurs. De kosten zijn afhankelijk van de ophaallocatie in Nederland en het afleveradres in Spanje en worden apart in rekening gebracht (excl. pakketprijs).',
        includes: [
            'Coördinatie via gecertificeerd transportpartner',
            'Ophalen op adres of bij exporteur in Nederland',
            'Aflevering op uw adres in Spanje',
            'Track & trace gedurende het transport',
            'Afstemming met ons Spaanse kantoor voor aankomst',
        ],
        cta: { label: 'Bekijk Compleet Plus pakket', href: '/tarieven' },
        accent: false,
    },
    {
        icon: ClipboardCheck,
        slug: 'aankoopbegeleiding',
        title: 'Aankoopbegeleiding Nederlandse auto',
        tagline: 'De juiste auto kopen in Nederland — zonder onaangename verrassingen achteraf.',
        description:
            'Wilt u in Nederland een auto kopen om vervolgens naar Spanje te importeren? Wij helpen u de juiste keuze te maken via ons uitgebreide dealernetwerk in Nederland. We denken mee over de CO₂-uitstoot (bepalend voor de Spaanse registratiebelasting), de leeftijd van de auto (bepalend voor BPM-teruggave) en de aanwezigheid van een COC-document. Deze dienst is ook los af te nemen — los van een import-pakket.',
        includes: [
            'Toegang tot ons uitgebreide dealernetwerk in Nederland',
            'Adviesgesprek van 30 minuten (telefonisch of video)',
            'CO₂-check: impact op de Spaanse IEDMT-belasting',
            'BPM-indicatie op basis van catalogusprijs en leeftijd',
            'Check op aanwezigheid van COC-document',
            'Advies over merk/model en importeerbaarheid',
            'Directe koppeling met exportpartner na aankoop',
        ],
        cta: { label: 'Bekijk Compleet Plus pakket', href: '/tarieven' },
        cta2: { label: 'Neem contact op', href: '/contact' },
        accent: false,
    },
    {
        icon: ShieldCheck,
        slug: 'itv-begeleiding',
        title: 'ITV-begeleiding',
        tagline: 'De Spaanse APK — wij regelen de afspraak en begeleiden u door het proces.',
        description:
            'De ITV (Inspección Técnica de Vehículos) is de Spaanse equivalent van de Nederlandse APK en is verplicht onderdeel van de matriculación. Wij regelen de afspraak bij een erkend keuringsstation in uw regio en informeren u over wat u mee moet nemen. Bij het Compleet Plus pakket gaan wij zelfs namens u naar de afspraak.',
        includes: [
            'Afspraak bij erkend ITV-station in uw buurt',
            'Voorbereiding: checklist met vereiste documenten',
            'Uitleg over het keuringsproces',
            'Begeleiding bij herkeuring indien nodig',
            'Namens u aanwezig (Compleet Plus)',
        ],
        cta: { label: 'Bekijk pakketten', href: '/tarieven' },
        accent: false,
    },
];

const steps = [
    { number: '01', title: 'Kenteken invoeren', body: 'Voer uw kenteken in op de homepage. U ziet direct een berekening van uw BPM-teruggave en de Spaanse import-kosten.' },
    { number: '02', title: 'Pakket kiezen', body: 'Kies het pakket dat bij uw situatie past — van zelf de regie houden tot volledig ontzorgd.' },
    { number: '03', title: 'Offerte aanvragen', body: 'Vul uw gegevens in. Wij nemen binnen één werkdag contact op om alles door te nemen.' },
    { number: '04', title: 'Wij regelen de rest', body: 'Afhankelijk van uw pakket verzorgen wij export, DGT-aanvraag, ITV en de definitieve platen.' },
];
</script>

<template>
    <PublicLayout
        title="Diensten — Alles voor uw auto op Spaans kenteken"
        description="Van BPM-teruggave en auto-export tot ITV-begeleiding en volledige matriculación in Spanje. Ontdek onze diensten voor Nederlanders aan de Costa del Sol."
    >
        <!-- Hero -->
        <section class="bg-background">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24">
                <span class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground">
                    Voor Nederlanders in Spanje
                </span>
                <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-5xl">
                    Alles geregeld,<br />
                    <span class="text-accent">niets aan de hand.</span>
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-muted-foreground">
                    Wij begeleiden het volledige traject om uw Nederlandse auto op Spaans kenteken te zetten — van BPM-teruggave in Nederland tot de definitieve platen in Spanje.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground transition-colors hover:bg-accent/90"
                    >
                        Bereken uw kosten
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        href="/tarieven"
                        class="inline-flex items-center gap-2 rounded-xl border border-border px-6 py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                    >
                        Bekijk tarieven
                    </Link>
                </div>
            </div>
        </section>

        <!-- Diensten -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="text-center">
                    <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                        Onze diensten
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm text-muted-foreground">
                        Vier gespecialiseerde diensten die samen het complete importtraject afdekken.
                    </p>
                </div>

                <div class="mt-12 space-y-10">
                    <div
                        v-for="service in services"
                        :key="service.slug"
                        class="grid gap-8 rounded-2xl border bg-card p-8 shadow-sm lg:grid-cols-2 lg:items-start"
                        :class="service.accent ? 'border-accent/40' : 'border-border'"
                    >
                        <!-- Left: info -->
                        <div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-11 items-center justify-center rounded-xl"
                                    :class="service.accent ? 'bg-accent/15 text-accent' : 'bg-muted text-muted-foreground'"
                                >
                                    <component :is="service.icon" class="size-5" />
                                </div>
                                <div>
                                    <div
                                        v-if="service.accent"
                                        class="text-xs font-semibold uppercase tracking-wider text-accent"
                                    >
                                        Hoofddienst
                                    </div>
                                    <h3 class="font-display text-xl font-semibold text-foreground">
                                        {{ service.title }}
                                    </h3>
                                </div>
                            </div>

                            <p class="mt-3 text-sm font-medium text-muted-foreground">
                                {{ service.tagline }}
                            </p>
                            <p class="mt-4 text-sm leading-relaxed text-foreground/80">
                                {{ service.description }}
                            </p>

                            <div class="mt-5 flex flex-wrap gap-4">
                                <Link
                                    :href="service.cta.href"
                                    class="inline-flex items-center gap-2 text-sm font-semibold text-accent transition-colors hover:underline"
                                >
                                    {{ service.cta.label }}
                                    <ArrowRight class="size-4" />
                                </Link>
                                <Link
                                    v-if="service.cta2"
                                    :href="service.cta2.href"
                                    class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground transition-colors hover:text-foreground hover:underline"
                                >
                                    {{ service.cta2.label }}
                                    <ArrowRight class="size-4" />
                                </Link>
                            </div>
                        </div>

                        <!-- Right: includes -->
                        <div class="rounded-xl bg-muted/60 p-6">
                            <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Inbegrepen
                            </div>
                            <ul class="mt-4 space-y-3">
                                <li
                                    v-for="item in service.includes"
                                    :key="item"
                                    class="flex items-start gap-3 text-sm"
                                >
                                    <CheckCircle class="mt-0.5 size-4 shrink-0 text-success" />
                                    <span class="text-foreground">{{ item }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hoe het werkt -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="text-center">
                    <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                        Hoe werkt het?
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm text-muted-foreground">
                        Van eerste berekening tot rijden op Spaans kenteken — in vier stappen.
                    </p>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="step in steps"
                        :key="step.number"
                        class="relative flex flex-col gap-3 rounded-2xl border border-border bg-card p-6"
                    >
                        <div class="font-display text-5xl font-bold text-accent/20 select-none">
                            {{ step.number }}
                        </div>
                        <h3 class="font-semibold text-foreground">{{ step.title }}</h3>
                        <p class="text-sm text-muted-foreground">{{ step.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-3xl px-4 py-16 text-center">
                <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                    Klaar om te starten?
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-muted-foreground">
                    Voer uw kenteken in en zie direct wat uw auto u oplevert — en wat het traject kost.
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground transition-colors hover:bg-accent/90"
                    >
                        Bereken mijn kosten
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        href="/tarieven"
                        class="inline-flex items-center gap-2 rounded-xl border border-border px-6 py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                    >
                        Bekijk tarieven
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
