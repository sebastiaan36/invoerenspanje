<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Check } from 'lucide-vue-next';
import ContactStrip from '@/components/ContactStrip.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface Service {
    id: string;
    title: string;
    description: string;
    includes: string[];
    cta?: { label: string; href: string };
}

interface Lane {
    country: string;
    authorities: string;
    services: Service[];
}

const quoteHref = '/#offerte';

const lanes: Lane[] = [
    {
        country: 'Nederland',
        authorities: 'RDW en Belastingdienst',
        services: [
            {
                id: 'auto-export-nederland',
                title: 'Auto-export Nederland',
                description:
                    'Voordat uw auto in Spanje ingeschreven kan worden, moet hij in Nederland worden afgemeld met de status export. Bij Compleet en Compleet Plus regelen wij dit via een van onze erkende RDW-exportpartners. Bij Basis regelt u de export zelf en sluiten wij daarop aan.',
                includes: [
                    'Exportmelding via een erkende RDW-exportpartner',
                    'Exportkenteken, veertien dagen geldig voor de rit naar Spanje',
                    'Exportdocumenten die de DGT (de Spaanse RDW) vraagt',
                ],
            },
            {
                id: 'bpm-teruggave',
                title: 'BPM-teruggave',
                description:
                    "Bij aankoop in Nederland is BPM (Belasting van Personenauto's en Motorrijwielen) betaald. Exporteert u de auto, dan heeft u recht op teruggave van de rest-BPM. Hoeveel dat is, hangt af van de leeftijd van de auto en de oorspronkelijke BPM. Wij dienen de aanvraag in bij de Belastingdienst.",
                includes: [
                    'Indicatie van de rest-BPM vooraf',
                    'Aanvraag bij de Belastingdienst op basis van de exportmelding',
                    'Afstemming van de timing met de Spaanse inschrijving',
                ],
                cta: {
                    label: 'Bereken uw BPM-teruggave',
                    href: '/bpm-calculator',
                },
            },
        ],
    },
    {
        country: 'Spanje',
        authorities: 'DGT en ITV',
        services: [
            {
                id: 'auto-op-spaans-kenteken',
                title: 'Auto op Spaans kenteken',
                description:
                    'Woont u meer dan 183 dagen per jaar in Spanje, dan moet uw auto op Spaans kenteken. Wij verzorgen de matriculación (de Spaanse inschrijving) bij de DGT, van de documenten tot de definitieve platen. U heeft er geen Spaans voor nodig.',
                includes: [
                    'Controle van uw documenten, zoals uw NIE (Spaans fiscaal nummer) en empadronamiento (inschrijving bij de gemeente)',
                    'Verwerking van uw COC, of een Ficha Técnica (technisch rapport) als er geen COC is, voor € 85 extra',
                    'Matrículas temporales: groene tijdelijke kentekenplaten om legaal naar de ITV te rijden (optioneel)',
                    'Berekening van de registratiebelasting (IEDMT) of de vrijstelling bij verhuizing',
                    'Aanvraag bij de DGT en communicatie in het Spaans',
                    'Definitieve Spaanse kentekenplaten',
                ],
            },
            {
                id: 'itv-begeleiding',
                title: 'ITV-begeleiding',
                description:
                    'De ITV (Inspección Técnica de Vehículos) is de Spaanse APK en een verplicht onderdeel van de inschrijving. Bij Compleet en Compleet Plus plannen wij de afspraak bij een erkend keuringsstation. Bij Compleet Plus gaan wij namens u naar de keuring.',
                includes: [
                    'Afspraak bij een erkend ITV-station in uw buurt',
                    'Checklist met de documenten die u meeneemt',
                    'Uitleg over het keuringsproces en hulp bij een herkeuring',
                    'Namens u aanwezig bij de keuring (Compleet Plus)',
                ],
            },
        ],
    },
];

const addOns = [
    {
        id: 'transport',
        title: 'Transport naar Spanje',
        body: 'Wilt u niet zelf rijden? Bij Compleet Plus regelen wij het transport via onze transportpartners, van uw adres in Nederland naar uw adres in Spanje. De transportkosten zelf komen apart en hangen af van ophaal- en afleveradres.',
        cta: { label: 'Bekijk Compleet Plus', href: '/tarieven#pakketten' },
    },
    {
        id: 'aankoopbegeleiding',
        title: 'Aankoopbegeleiding',
        body: 'Koopt u nog een auto in Nederland om mee te nemen? In een adviesgesprek van 30 minuten kijken we naar CO₂-uitstoot (bepaalt de Spaanse registratiebelasting), leeftijd (bepaalt de BPM-teruggave) en of er een COC is. Inbegrepen bij Compleet Plus, of los af te nemen.',
        cta: { label: 'Bekijk Compleet Plus', href: '/tarieven#pakketten' },
    },
];
</script>

<template>
    <PublicLayout>
        <!-- Hero -->
        <section class="bg-background">
            <div
                class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24"
            >
                <h1
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-5xl"
                >
                    Eén dossier, van
                    <span class="text-accent">RDW tot Spaans kenteken</span>
                </h1>
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground"
                >
                    Wij begeleiden het volledige traject om uw Nederlandse auto
                    op Spaans kenteken te zetten: de export en BPM-teruggave in
                    Nederland, de keuring en inschrijving in Spanje.
                </p>
                <div
                    class="mt-8 flex flex-wrap items-center justify-center gap-3"
                >
                    <Link
                        :href="quoteHref"
                        class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        Bereken uw kosten en vraag een offerte aan
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        href="/tarieven#pakketten"
                        class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-border px-6 py-3 text-sm font-semibold text-foreground transition-colors hover:bg-secondary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        Bekijk de pakketten
                    </Link>
                </div>
            </div>
        </section>

        <!-- Het dossier: Nederland en Spanje -->
        <section
            class="border-t border-border bg-card"
            aria-labelledby="dossier-title"
        >
            <div class="container mx-auto max-w-5xl px-4 py-16 md:py-24">
                <h2
                    id="dossier-title"
                    class="max-w-2xl font-display text-3xl font-semibold text-balance text-foreground md:text-4xl"
                >
                    Wat wij voor u regelen
                </h2>
                <p class="mt-4 max-w-2xl text-lg text-muted-foreground">
                    Uw auto doorloopt een traject in Nederland en een traject in
                    Spanje. Wij stemmen beide op elkaar af, zodat u één
                    aanspreekpunt heeft. Welke stappen wij overnemen, hangt af
                    van uw pakket.
                </p>

                <div class="mt-12 space-y-16">
                    <div v-for="(lane, laneIndex) in lanes" :key="lane.country">
                        <h3
                            class="flex flex-wrap items-baseline gap-x-3 gap-y-1 border-b border-border pb-4 font-display text-2xl font-semibold text-foreground"
                        >
                            <span
                                class="size-2.5 shrink-0 self-center rounded-full"
                                :class="
                                    laneIndex === 0 ? 'bg-primary' : 'bg-accent'
                                "
                                aria-hidden="true"
                            />
                            {{ lane.country }}
                            <span
                                class="font-sans text-sm font-normal text-muted-foreground"
                                >{{ lane.authorities }}</span
                            >
                        </h3>

                        <div class="divide-y divide-border">
                            <article
                                v-for="service in lane.services"
                                :id="service.id"
                                :key="service.id"
                                class="grid scroll-mt-24 gap-6 py-10 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] md:gap-12"
                            >
                                <div>
                                    <h4
                                        class="font-display text-xl font-semibold text-foreground"
                                    >
                                        {{ service.title }}
                                    </h4>
                                    <p
                                        class="mt-3 leading-relaxed text-foreground/80"
                                    >
                                        {{ service.description }}
                                    </p>
                                    <Link
                                        v-if="service.cta"
                                        :href="service.cta.href"
                                        class="mt-3 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary underline-offset-4 hover:underline"
                                    >
                                        {{ service.cta.label }}
                                        <ArrowRight class="size-4" />
                                    </Link>
                                </div>
                                <ul class="space-y-3">
                                    <li
                                        v-for="item in service.includes"
                                        :key="item"
                                        class="flex items-start gap-3"
                                    >
                                        <Check
                                            class="mt-1 size-4 shrink-0 stroke-[3] text-success"
                                        />
                                        <span class="text-foreground">{{
                                            item
                                        }}</span>
                                    </li>
                                </ul>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Aanvullend -->
        <section
            class="border-t border-border bg-secondary/30"
            aria-labelledby="addons-title"
        >
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <h2
                    id="addons-title"
                    class="font-display text-2xl font-semibold text-foreground sm:text-3xl"
                >
                    Daarnaast mogelijk
                </h2>
                <div class="mt-8 divide-y divide-border border-y border-border">
                    <div
                        v-for="addOn in addOns"
                        :id="addOn.id"
                        :key="addOn.id"
                        class="grid scroll-mt-24 gap-3 py-6 md:grid-cols-[12rem_minmax(0,1fr)_auto] md:items-center md:gap-8"
                    >
                        <h3 class="font-semibold text-foreground">
                            {{ addOn.title }}
                        </h3>
                        <p class="text-muted-foreground">
                            {{ addOn.body }}
                        </p>
                        <Link
                            :href="addOn.cta.href"
                            class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold whitespace-nowrap text-primary underline-offset-4 hover:underline"
                        >
                            {{ addOn.cta.label }}
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Afsluiting -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16 md:py-20">
                <div class="text-center">
                    <h2
                        class="font-display text-2xl font-semibold text-balance text-foreground sm:text-3xl"
                    >
                        Weten wat het u kost?
                    </h2>
                    <p class="mx-auto mt-4 max-w-lg text-muted-foreground">
                        Vul uw kenteken in en zie direct de BPM-teruggave en de
                        kosten in Spanje. Daarna vraagt u met één formulier een
                        offerte aan.
                    </p>
                    <Link
                        :href="quoteHref"
                        class="mt-6 inline-flex min-h-11 items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        Bereken uw kosten en vraag een offerte aan
                        <ArrowRight class="size-4" />
                    </Link>
                </div>

                <ContactStrip class="mt-12" />
            </div>
        </section>
    </PublicLayout>
</template>
