<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Check, Star } from 'lucide-vue-next';
import { computed } from 'vue';
import ContactStrip from '@/components/ContactStrip.vue';
import FaqAccordion from '@/components/FaqAccordion.vue';
import type { FaqItem } from '@/components/FaqAccordion.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { trackEvent } from '@/lib/analytics';

interface ServicePackage {
    slug: string;
    name: string;
    price_eur: number;
    tagline: string;
    recommended: boolean;
    features: string[];
}

interface FixedCost {
    key: string;
    label: string;
    amount_eur: number;
}

const props = withDefaults(
    defineProps<{
        fixedCosts?: FixedCost[];
        faqItems?: FaqItem[];
    }>(),
    {
        fixedCosts: () => [],
        faqItems: () => [],
    },
);

const packages = usePage().props.packages as ServicePackage[];

// Same example as the guide (resources/views/content/gids.blade.php): a car
// with a COC (no Ficha Técnica), temporary plates and IEDMT at 0%.
const EXAMPLE_COST_KEYS = [
    'tasas',
    'placas_definitivas',
    'honorarios',
    'matriculas_temporales',
    'itv_keuring',
];

const workedExample = computed(() => {
    const pkg = packages.find((p) => p.slug === 'compleet');

    if (!pkg || props.fixedCosts.length === 0) {
        return null;
    }

    const spanish = props.fixedCosts
        .filter((cost) => EXAMPLE_COST_KEYS.includes(cost.key))
        .reduce((sum, cost) => sum + cost.amount_eur, 0);

    return {
        packageName: pkg.name,
        packagePrice: pkg.price_eur,
        spanish,
        total: pkg.price_eur + spanish,
    };
});

const quoteHref = '/#offerte';

const wholeEuroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

const centsEuroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

function formatEuro(value: number): string {
    return Number.isInteger(value)
        ? wholeEuroFormatter.format(value)
        : centsEuroFormatter.format(value);
}

/**
 * Short clarifications shown under specific fixed-cost rows.
 */
const fixedCostNotes: Record<string, string> = {
    ficha_tecnica: 'Vervalt als u een COC-document aanlevert.',
    matriculas_temporales:
        'Nodig om met de geëxporteerde auto naar de ITV te rijden.',
    itv_keuring: 'Betaalt u zelf aan de balie van het keuringsstation.',
};

const steps = [
    {
        country: 'Nederland',
        title: 'Export in Nederland',
        body: 'Uw auto wordt bij de RDW afgemeld met de status export. Bij Basis regelt u dit zelf, bij Compleet en Compleet Plus gaat het via een van onze erkende RDW-exportpartners.',
    },
    {
        country: 'Spanje',
        title: 'Documenten en COC of Ficha Técnica',
        body: 'Wij controleren uw documenten. Levert u een COC aan, dan is geen Ficha Técnica (technisch rapport) nodig. Zonder COC wordt die opgesteld voor € 85 extra.',
    },
    {
        country: 'Spanje',
        title: 'Tijdelijke kentekenplaten',
        body: 'Met groene matrículas temporales (tijdelijke kentekenplaten) rijdt u legaal naar de ITV. Wij vragen ze aan; bij Basis haalt u ze zelf op.',
    },
    {
        country: 'Spanje',
        title: 'ITV-keuring',
        body: 'De Spaanse APK, die u zelf betaalt (ca. € 150–160). Bij Basis plant u de afspraak zelf, bij Compleet plannen wij die en bij Compleet Plus gaan wij namens u.',
    },
    {
        country: 'Spanje',
        title: 'Definitieve Spaanse kentekenplaten',
        body: 'Na de keuring en de registratiebelasting (of vrijstelling) verwerken wij de aanvraag bij de DGT, de Spaanse RDW. Daarna volgen kentekenbewijs en platen; bij Basis haalt u de platen zelf op.',
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
                    Wat kost het om uw auto
                    <span class="text-accent">op Spaans kenteken</span> te
                    zetten?
                </h1>
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground"
                >
                    Een vaste prijs voor onze begeleiding, plus de vaste kosten
                    in Spanje die wij hieronder voor u op een rij zetten. Geen
                    verrassingen achteraf.
                </p>
            </div>
        </section>

        <!-- Het traject -->
        <section
            class="border-t border-border bg-card"
            aria-labelledby="traject-title"
        >
            <div class="container mx-auto max-w-2xl px-4 py-16">
                <h2
                    id="traject-title"
                    class="font-display text-2xl font-semibold text-balance text-foreground sm:text-3xl"
                >
                    Hoe verloopt het traject?
                </h2>
                <p class="mt-3 max-w-xl text-muted-foreground">
                    Deze stappen doorloopt elke auto, in deze volgorde. Uw
                    pakket bepaalt welke stappen wij voor u overnemen.
                </p>

                <ol class="mt-10">
                    <li
                        v-for="(step, index) in steps"
                        :key="step.title"
                        class="relative flex gap-5 pb-8 last:pb-0"
                    >
                        <span
                            v-if="index < steps.length - 1"
                            class="absolute top-10 bottom-0 left-[1.1875rem] w-px bg-border"
                            aria-hidden="true"
                        />
                        <span
                            class="relative flex size-10 shrink-0 items-center justify-center rounded-full border text-sm font-semibold tabular-nums"
                            :class="
                                step.country === 'Nederland'
                                    ? 'border-primary/30 bg-primary/10 text-primary'
                                    : 'border-border bg-background text-foreground'
                            "
                        >
                            {{ index + 1 }}
                        </span>
                        <div class="pt-1.5">
                            <h3
                                class="flex flex-wrap items-baseline gap-x-3 gap-y-1 font-semibold text-foreground"
                            >
                                {{ step.title }}
                                <span
                                    class="text-sm font-normal text-muted-foreground"
                                    >{{ step.country }}</span
                                >
                            </h3>
                            <p class="mt-1 text-muted-foreground">
                                {{ step.body }}
                            </p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Vaste kosten -->
        <section
            class="border-t border-border bg-background"
            aria-labelledby="kosten-title"
        >
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
                    <div>
                        <h2
                            id="kosten-title"
                            class="font-display text-2xl font-semibold text-balance text-foreground sm:text-3xl"
                        >
                            Pakketprijs en vaste kosten
                        </h2>
                        <p class="mt-4 text-muted-foreground">
                            Onze pakketprijs dekt de begeleiding en de
                            administratieve verwerking door ons. Daarnaast komen
                            de vaste kosten in Spanje: leges, platen, keuring en
                            het Spaanse administratiekantoor. Die bedragen
                            bepalen wij niet zelf.
                        </p>
                        <p class="mt-3 text-muted-foreground">
                            De registratiebelasting (IEDMT) hangt af van de
                            CO₂-uitstoot en de waarde van uw auto. Met de
                            calculator op de homepage ziet u wat dat voor uw
                            auto betekent.
                        </p>
                        <div
                            v-if="workedExample"
                            class="mt-6 rounded-xl bg-muted/60 p-5"
                        >
                            <h3
                                class="font-display text-lg font-semibold text-foreground"
                            >
                                Rekenvoorbeeld
                            </h3>
                            <p class="mt-2 text-muted-foreground">
                                Een auto met COC-document, uitstoot onder de 120
                                g/km en tijdelijke kentekenplaten.
                            </p>
                            <dl class="mt-4 space-y-2 tabular-nums">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-muted-foreground">
                                        Pakket {{ workedExample.packageName }}
                                    </dt>
                                    <dd class="font-medium text-foreground">
                                        {{
                                            formatEuro(
                                                workedExample.packagePrice,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-muted-foreground">
                                        Vaste kosten in Spanje
                                    </dt>
                                    <dd class="font-medium text-foreground">
                                        {{ formatEuro(workedExample.spanish) }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-muted-foreground">
                                        Registratiebelasting (IEDMT)
                                    </dt>
                                    <dd class="font-medium text-foreground">
                                        {{ formatEuro(0) }}
                                    </dd>
                                </div>
                                <div
                                    class="flex justify-between gap-4 border-t border-border pt-2"
                                >
                                    <dt class="font-semibold text-foreground">
                                        U betaalt
                                    </dt>
                                    <dd
                                        class="font-display text-lg font-semibold text-foreground"
                                    >
                                        {{ formatEuro(workedExample.total) }}
                                    </dd>
                                </div>
                            </dl>
                            <p class="mt-3 text-sm text-muted-foreground">
                                Daar staat de BPM-teruggave uit Nederland
                                tegenover. Die hangt af van de leeftijd en de
                                oorspronkelijke BPM van uw auto.
                            </p>
                        </div>
                        <Link
                            :href="quoteHref"
                            class="mt-3 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary underline-offset-4 hover:underline"
                        >
                            Bereken uw kosten en vraag een offerte aan
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            href="/auto-invoeren-spanje"
                            class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary underline-offset-4 hover:underline"
                        >
                            Lees de complete gids auto invoeren in Spanje
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>

                    <div
                        class="rounded-2xl border border-border bg-card p-6 shadow-sm"
                    >
                        <h3
                            class="font-display text-xl font-semibold text-foreground"
                        >
                            Vaste kosten in Spanje
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Bovenop de pakketprijs. Welke posten voor u gelden,
                            hangt af van uw auto en uw keuzes.
                        </p>
                        <ul class="mt-4 divide-y divide-border">
                            <li
                                v-for="cost in fixedCosts"
                                :key="cost.key"
                                class="flex items-baseline justify-between gap-4 py-3"
                            >
                                <div class="min-w-0">
                                    <p class="text-foreground">
                                        {{ cost.label }}
                                    </p>
                                    <p
                                        v-if="fixedCostNotes[cost.key]"
                                        class="mt-0.5 text-sm text-muted-foreground"
                                    >
                                        {{ fixedCostNotes[cost.key] }}
                                    </p>
                                </div>
                                <span
                                    class="text-right font-medium whitespace-nowrap text-foreground tabular-nums"
                                    >{{ formatEuro(cost.amount_eur) }}</span
                                >
                            </li>
                            <li class="py-3">
                                <div
                                    class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1"
                                >
                                    <p class="text-foreground">
                                        IEDMT (Spaanse registratiebelasting)
                                    </p>
                                    <span
                                        class="text-right text-muted-foreground"
                                        >afhankelijk van CO₂-uitstoot</span
                                    >
                                </div>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    0% tot 120 g/km, daarboven 4,75%, 9,75% of
                                    14,75% van de waarde van de auto. Verhuist u
                                    uw hoofdverblijf naar Spanje en staat de
                                    auto al minstens zes maanden op uw naam, dan
                                    kunt u vrijstelling krijgen.
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pakketten -->
        <section
            id="pakketten"
            class="scroll-mt-24 border-t border-border bg-muted/40"
            aria-labelledby="pakketten-title"
        >
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="text-center">
                    <h2
                        id="pakketten-title"
                        class="font-display text-2xl font-semibold text-foreground sm:text-3xl"
                    >
                        Kies uw pakket
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-muted-foreground">
                        Drie vaste pakketten, van zelf de regie houden tot
                        volledig ontzorgd worden. U kiest hoeveel u uit handen
                        geeft.
                    </p>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-3 md:items-stretch">
                    <div
                        v-for="pkg in packages"
                        :key="pkg.slug"
                        class="relative flex flex-col overflow-hidden rounded-2xl bg-card p-5 sm:p-8"
                        :class="{
                            'border-2 border-accent shadow-lg': pkg.recommended,
                            'border border-border shadow-sm': !pkg.recommended,
                        }"
                    >
                        <div
                            v-if="pkg.recommended"
                            class="absolute -top-px left-8 inline-flex items-center gap-1.5 rounded-b-md bg-accent px-3 py-1 text-sm font-semibold text-accent-foreground"
                        >
                            <Star class="size-3.5 fill-current" />
                            Aanbevolen
                        </div>

                        <div :class="pkg.recommended ? 'mt-5' : ''">
                            <h3
                                class="font-display text-2xl font-semibold text-primary"
                            >
                                {{ pkg.name }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ pkg.tagline }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <span
                                class="font-display text-4xl font-semibold text-foreground tabular-nums sm:text-5xl"
                            >
                                {{ formatEuro(pkg.price_eur) }}
                            </span>
                            <span class="ml-1 text-sm text-muted-foreground"
                                >eenmalig</span
                            >
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Exclusief vaste kosten in Spanje en eventueel
                            transport
                        </p>

                        <hr class="my-6 border-border" />

                        <ul class="flex-1 space-y-3 text-sm">
                            <li
                                v-for="feature in pkg.features"
                                :key="feature"
                                class="flex items-start gap-3"
                            >
                                <Check
                                    class="mt-0.5 size-4 shrink-0 stroke-[3] text-success"
                                />
                                <span class="text-foreground">{{
                                    feature
                                }}</span>
                            </li>
                        </ul>

                        <Link
                            :href="`/?pakket=${pkg.slug}#offerte`"
                            @click="
                                trackEvent('select_package', {
                                    package_slug: pkg.slug,
                                    package_price: pkg.price_eur,
                                    location: 'tarieven',
                                })
                            "
                            class="mt-8 flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                            :class="{
                                'bg-accent text-accent-foreground shadow-sm hover:bg-accent/90':
                                    pkg.recommended,
                                'border border-border bg-background text-foreground hover:bg-secondary':
                                    !pkg.recommended,
                            }"
                        >
                            Kies {{ pkg.name }}
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                </div>

                <ContactStrip
                    class="mt-12"
                    body="Twijfelt u welk pakket bij u past? Bel of app Maikel, hij denkt graag mee."
                />
            </div>
        </section>

        <!-- Veelgestelde vragen -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-2xl px-4 py-16">
                <h2
                    class="text-center font-display text-2xl font-semibold text-foreground sm:text-3xl"
                >
                    Veelgestelde vragen
                </h2>

                <FaqAccordion :items="faqItems" class="mt-10" />
            </div>
        </section>

        <!-- Afsluiting -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-3xl px-4 py-16 text-center">
                <h2
                    class="font-display text-2xl font-semibold text-balance text-foreground sm:text-3xl"
                >
                    Wat kost het voor uw auto?
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-muted-foreground">
                    Vul uw kenteken in en zie direct de totale kosten voor uw
                    auto, met de BPM-teruggave en de Spaanse
                    registratiebelasting al meegerekend.
                </p>
                <Link
                    :href="quoteHref"
                    class="mt-6 inline-flex min-h-11 items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                >
                    Bereken uw kosten en vraag een offerte aan
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>
