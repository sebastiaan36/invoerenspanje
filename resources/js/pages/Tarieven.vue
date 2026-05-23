<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Check, Star, ArrowRight, FileText, Car, ClipboardCheck, ShieldCheck } from 'lucide-vue-next';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface ServicePackage {
    slug: string;
    name: string;
    price_eur: number;
    tagline: string;
    recommended: boolean;
    features: string[];
}

const packages = usePage().props.packages as ServicePackage[];

const euroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

function formatEuro(value: number): string {
    return euroFormatter.format(value);
}

const steps = [
    {
        icon: Car,
        title: 'Export in Nederland',
        body: 'Uw auto wordt uitgeschreven bij de RDW. Dit kan via uw eigen exporteur of via een van onze partners in Nederland.',
    },
    {
        icon: FileText,
        title: 'Documenten & Ficha Técnica',
        body: 'Wij verzamelen de benodigde documenten. Als u een COC aanlevert is de Ficha Técnica al geregeld — anders maken wij die voor u (€ 85 extra).',
    },
    {
        icon: ClipboardCheck,
        title: 'Aanvraag bij de DGT',
        body: 'Wij verwerken de volledige matriculación-aanvraag bij de Dirección General de Tráfico. U hoeft zelf geen Spaans te spreken.',
    },
    {
        icon: ShieldCheck,
        title: 'ITV & kenteken',
        body: 'Na de ITV-keuring (betaalt u zelf, ca. € 150–160) leveren wij de groene matrículas temporales en daarna de definitieve platen.',
    },
];

const extraCosts = [
    { label: 'Tasas DGT (registratietaks)', amount: '€ 125' },
    { label: 'Ficha Técnica', amount: '€ 85 (vervalt met COC)' },
    { label: 'Placas definitivas', amount: '€ 35' },
    { label: 'Matrículas Temporales (opt.)', amount: '€ 150' },
    { label: 'ITV-keuring (klant betaalt zelf)', amount: 'ca. € 150–160' },
    { label: 'IEDMT registratiebelasting', amount: 'afhankelijk van CO₂ en leeftijd' },
];
</script>

<template>
    <PublicLayout
        title="Tarieven — Auto op Spaans kenteken"
        description="Transparante vaste prijzen voor het omzetten van uw Nederlandse auto naar Spaans kenteken. Bekijk onze drie pakketten en wat er bij inbegrepen is."
    >
        <!-- Hero -->
        <section class="bg-background">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24">
                <span class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground">
                    Transparante prijzen
                </span>
                <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-5xl">
                    Wat kost het om uw auto<br />
                    <span class="text-accent">op Spaans kenteken</span> te zetten?
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-muted-foreground">
                    Een vaste servicevergoeding voor onze begeleiding — plus de officiële overheidskosten die voor iedereen gelden. Geen verrassingen achteraf.
                </p>
            </div>
        </section>

        <!-- Hoe het werkt -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="text-center">
                    <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                        Hoe werkt het traject?
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm text-muted-foreground">
                        Van export in Nederland tot definitief Spaans kenteken — dit zijn de vier stappen die altijd doorlopen worden, ongeacht het pakket.
                    </p>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(step, index) in steps"
                        :key="step.title"
                        class="relative flex flex-col gap-3 rounded-2xl border border-border bg-card p-6"
                    >
                        <div class="flex size-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
                            <component :is="step.icon" class="size-5" />
                        </div>
                        <div class="absolute right-4 top-4 font-display text-4xl font-bold text-muted/30 select-none">
                            {{ index + 1 }}
                        </div>
                        <h3 class="font-semibold text-foreground">{{ step.title }}</h3>
                        <p class="text-sm text-muted-foreground">{{ step.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Overheidskosten -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
                    <div>
                        <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                            Servicekosten vs. overheidskosten
                        </h2>
                        <p class="mt-4 text-muted-foreground">
                            Onze pakketprijs dekt uitsluitend de begeleiding en administratieve verwerking door ons. Bovenop de pakketprijs komen altijd officiële overheidskosten die wij niet kunnen beïnvloeden.
                        </p>
                        <p class="mt-3 text-muted-foreground">
                            De exacte hoogte van de IEDMT-registratiebelasting hangt af van de CO₂-uitstoot en de leeftijd van uw auto. Gebruik de calculator op de homepage voor een persoonlijke indicatie.
                        </p>
                        <Link
                            href="/"
                            class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-accent hover:underline"
                        >
                            Bereken uw kosten
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6">
                        <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            Overheidskostenoverzicht
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Deze kosten komen bij het pakket en gelden voor elke auto.
                        </p>
                        <ul class="mt-5 divide-y divide-border">
                            <li
                                v-for="item in extraCosts"
                                :key="item.label"
                                class="flex items-baseline justify-between gap-4 py-3 text-sm"
                            >
                                <span class="text-foreground">{{ item.label }}</span>
                                <span class="shrink-0 tabular-nums text-muted-foreground">{{ item.amount }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pakketten -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="text-center">
                    <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                        Kies uw pakket
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm text-muted-foreground">
                        Drie vaste pakketten — van zelf de regie houden tot volledig ontzorgd worden. U kiest hoeveel u uit handen geeft.
                    </p>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-3 md:items-stretch">
                    <div
                        v-for="pkg in packages"
                        :key="pkg.slug"
                        class="relative flex flex-col overflow-hidden rounded-2xl border bg-card p-5 sm:p-8"
                        :class="{
                            'border-2 border-accent shadow-lg': pkg.recommended,
                            'border border-border shadow-sm': !pkg.recommended,
                        }"
                    >
                        <!-- Aanbevolen badge -->
                        <div
                            v-if="pkg.recommended"
                            class="absolute -top-px left-8 inline-flex items-center gap-1 rounded-b-md bg-accent px-2.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent-foreground"
                        >
                            <Star class="size-3 fill-current" />
                            Aanbevolen
                        </div>

                        <div :class="pkg.recommended ? 'mt-4' : ''">
                            <h3 class="font-display text-2xl font-semibold text-primary">
                                {{ pkg.name }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">{{ pkg.tagline }}</p>
                        </div>

                        <div class="mt-6">
                            <span class="font-display text-4xl font-semibold tabular-nums text-accent sm:text-5xl">
                                {{ formatEuro(pkg.price_eur) }}
                            </span>
                            <span class="ml-1 text-sm text-muted-foreground">eenmalig</span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">Excl. overheidskosten en eventueel transport</p>

                        <hr class="my-6 border-border" />

                        <ul class="flex-1 space-y-3 text-sm">
                            <li
                                v-for="feature in pkg.features"
                                :key="feature"
                                class="flex items-start gap-3"
                            >
                                <Check class="mt-0.5 size-4 shrink-0 stroke-[3] text-success" />
                                <span class="text-foreground">{{ feature }}</span>
                            </li>
                        </ul>

                        <Link
                            href="/"
                            class="mt-8 flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold transition-colors"
                            :class="{
                                'bg-accent text-accent-foreground hover:bg-accent/90': pkg.recommended,
                                'bg-muted text-foreground hover:bg-muted/70': !pkg.recommended,
                            }"
                        >
                            Direct berekenen
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Veelgestelde vragen -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-3xl px-4 py-16">
                <h2 class="text-center font-display text-2xl font-semibold text-foreground sm:text-3xl">
                    Veelgestelde vragen
                </h2>

                <dl class="mt-10 space-y-8">
                    <div>
                        <dt class="font-semibold text-foreground">Welke documenten moet ik aanleveren?</dt>
                        <dd class="mt-2 text-sm text-muted-foreground">
                            Voor de standaard matriculación heeft u de volgende documenten nodig: een geldig paspoort of ID-kaart, uw NIE-nummer (Spaans fiscaal identificatienummer), het Nederlandse kentekenbewijs (deel 1A en 1B), de exportverklaring van de RDW, een COC-document (of wij regelen de Ficha Técnica voor u) en een bewijs van adres in Spanje (empadronamiento of huurcontract). Afhankelijk van uw situatie en het gekozen pakket begeleiden wij u stap voor stap bij het verzamelen van alle benodigde stukken.
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-foreground">Is de ITV-keuring bij de prijs inbegrepen?</dt>
                        <dd class="mt-2 text-sm text-muted-foreground">
                            Nee. De ITV-keuring betaalt u zelf aan de balie van het keuringsstation — dit is circa € 150 tot € 160 afhankelijk van het station. Wij regelen de afspraak voor u (Compleet en Compleet Plus), maar de kosten zijn voor uw eigen rekening.
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-foreground">Wat is de Ficha Técnica en wanneer heb ik die nodig?</dt>
                        <dd class="mt-2 text-sm text-muted-foreground">
                            De Ficha Técnica is het Spaanse technische document van uw auto. Als u een COC-document (Certificaat van Overeenstemming) aanlevert, vervalt de Ficha Técnica en bespaart u € 85. Heeft u geen COC, dan regelen wij de Ficha Técnica voor u — inclusief foto's op locatie bij het Compleet-pakket.
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-foreground">Wat zijn Matrículas Temporales?</dt>
                        <dd class="mt-2 text-sm text-muted-foreground">
                            Dit zijn tijdelijke groene kentekenplaten. Ze zijn niet verplicht, maar wel nodig om met een geëxporteerde auto legaal te blijven rijden en naar de ITV te gaan. Kosten: € 150.
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-foreground">Wat kost het transport van Nederland naar Spanje?</dt>
                        <dd class="mt-2 text-sm text-muted-foreground">
                            Wij regelen het transport via een van onze partners (Compleet Plus), maar de transportkosten worden apart in rekening gebracht en zijn afhankelijk van ophaallocatie en bestemming. Vraag een offerte aan via de calculator.
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-foreground">Kan ik een pakket wijzigen nadat ik een offerte heb aangevraagd?</dt>
                        <dd class="mt-2 text-sm text-muted-foreground">
                            Ja, zolang de aanvraag nog niet is ingediend bij de DGT kunt u van pakket wisselen. Neem contact met ons op via het klantenportaal.
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <!-- CTA -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-3xl px-4 py-16 text-center">
                <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                    Klaar om te beginnen?
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-muted-foreground">
                    Voer uw kenteken in en zie direct wat de totale kosten zijn voor uw specifieke auto — inclusief BPM-teruggave en Spaanse registratiebelasting.
                </p>
                <Link
                    href="/"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground transition-colors hover:bg-accent/90"
                >
                    Bereken mijn kosten
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>
