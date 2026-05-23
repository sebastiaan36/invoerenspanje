<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Car, Globe, Users, Award, Handshake } from 'lucide-vue-next';
import PublicLayout from '@/layouts/PublicLayout.vue';

const team = [
    {
        name: 'Maikel',
        photo: '/images/maikel.jpeg',
        company: 'Maikel Mobility',
        role: 'Auto-expert & Spaanse importspecialist',
        bio: 'Maikel is de drijvende kracht achter het Spaanse importtraject. Met een diepgewortelde passie voor auto\'s heeft hij HBO Automotive gestudeerd aan de IVA en jarenlange ervaring opgebouwd in de branche. Hij kent de weg bij de DGT op zijn duimpje. Van Ficha Técnica tot ITV — Maikel regelt het.',
        highlights: [
            'HBO Automotive — IVA (International Vehicle Academy)',
            'Jarenlange ervaring in de autobranche',
            'Directe lijnen bij de DGT en Spaanse overheidsinstanties',
            'Breed netwerk van RDW-exportpartners in Nederland',
            'Gecertificeerde ITV-begeleiding in de regio Málaga',
        ],
        icon: Car,
        accent: true,
    },
    {
        name: 'Sebastiaan',
        photo: '/images/sebastiaan.jpeg',
        company: 'Been & van Dam',
        role: 'Website bouwen & online marketing',
        bio: 'Sebastiaan zorgt ervoor dat autoinvoerenspanje.nl werkt zoals het hoort. Als website-ontwikkelaar en online marketeer bouwt hij de digitale infrastructuur waarmee klanten eenvoudig hun kenteken kunnen invoeren, een berekening krijgen en een offerte kunnen aanvragen. Hij verbindt de techniek met de markt.',
        highlights: [
            'Ontwikkelaar van autoinvoerenspanje.nl',
            'Online marketing en zichtbaarheid in Google',
            'Klantportaal en dossierbeheer',
            'BPM- en importkostencalculator',
            'Automatische lead- en offerteafhandeling',
        ],
        icon: Globe,
        accent: false,
    },
];

const values = [
    {
        icon: Handshake,
        title: 'Transparantie',
        body: 'Vaste prijzen vooraf, geen verborgen kosten. U weet precies waar u aan toe bent voordat u akkoord gaat.',
    },
    {
        icon: Users,
        title: 'Nederlandstalig',
        body: 'Wij communiceren in het Nederlands — geen taalbarrière, geen misverstanden. U hoeft zelf geen Spaans te spreken.',
    },
    {
        icon: Award,
        title: 'Gecertificeerd',
        body: 'Maikel heeft HBO Automotive gestudeerd aan de IVA en werkt met erkende RDW-exportpartners en officiële Spaanse keuringsstations.',
    },
    {
        icon: Car,
        title: 'Auto-expertise',
        body: 'Van aankoopadvies in Nederland tot definitieve platen in Spanje — wij kennen het complete traject van binnen en buiten.',
    },
];
</script>

<template>
    <PublicLayout
        title="Over ons — autoinvoerenspanje.nl"
        description="Leer Maikel en Sebastiaan kennen — de samenwerking achter autoinvoerenspanje.nl. IVA-gecertificeerde autospecialist en online marketeer voor Nederlanders in Spanje."
    >
        <!-- Hero -->
        <section class="bg-background">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24">
                <span class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground">
                    Twee specialisten, één missie
                </span>
                <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-5xl">
                    Over ons
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-muted-foreground">
                    autoinvoerenspanje.nl is een samenwerking tussen een IVA-gecertificeerde autospecialist en een online marketeer. Samen maken wij het importtraject eenvoudig, transparant en volledig Nederlandstalig.
                </p>
            </div>
        </section>

        <!-- Missie -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-3xl px-4 py-16 text-center">
                <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                    Onze missie
                </h2>
                <p class="mx-auto mt-5 max-w-2xl text-muted-foreground">
                    Duizenden Nederlanders wonen of verblijven langdurig aan de Costa del Sol. Zij willen hun vertrouwde auto meenemen naar Spanje, maar lopen vast op bureaucratie, taalbarrières en een ondoorzichtig systeem. Wij zetten dat recht: één aanspreekpunt, vaste prijs, alles geregeld.
                </p>
            </div>
        </section>

        <!-- Team -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="text-center">
                    <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                        Het team
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm text-muted-foreground">
                        Twee specialisten met elk hun eigen vakgebied — samen zorgen zij voor het complete plaatje.
                    </p>
                </div>

                <div class="mt-12 grid gap-8 lg:grid-cols-2">
                    <div
                        v-for="member in team"
                        :key="member.name"
                        class="flex flex-col overflow-hidden rounded-2xl border bg-card shadow-sm"
                        :class="member.accent ? 'border-accent/40' : 'border-border'"
                    >
                        <!-- Foto bovenaan -->
                        <div class="h-96 w-full overflow-hidden bg-muted">
                            <img
                                v-if="member.photo"
                                :src="member.photo"
                                :alt="member.name"
                                class="h-full w-full object-cover object-top"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-6xl font-bold"
                                :class="member.accent ? 'bg-accent/15 text-accent' : 'bg-muted text-muted-foreground'"
                            >
                                {{ member.name[0] }}
                            </div>
                        </div>

                        <!-- Tekst -->
                        <div class="flex flex-col p-8">
                            <div>
                                <h3 class="font-display text-xl font-semibold text-foreground">
                                    {{ member.name }}
                                </h3>
                                <div class="text-sm font-medium text-accent">{{ member.company }}</div>
                                <div class="text-xs text-muted-foreground">{{ member.role }}</div>
                            </div>

                            <p class="mt-4 text-sm leading-relaxed text-foreground/80">
                                {{ member.bio }}
                            </p>

                            <ul class="mt-5 space-y-2">
                                <li
                                    v-for="highlight in member.highlights"
                                    :key="highlight"
                                    class="flex items-start gap-2.5 text-sm"
                                >
                                    <component
                                        :is="member.icon"
                                        class="mt-0.5 size-4 shrink-0"
                                        :class="member.accent ? 'text-accent' : 'text-muted-foreground'"
                                    />
                                    <span class="text-foreground">{{ highlight }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Waarden -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="text-center">
                    <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                        Waar wij voor staan
                    </h2>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="value in values"
                        :key="value.title"
                        class="flex flex-col gap-3 rounded-2xl border border-border bg-card p-6"
                    >
                        <div class="flex size-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
                            <component :is="value.icon" class="size-5" />
                        </div>
                        <h3 class="font-semibold text-foreground">{{ value.title }}</h3>
                        <p class="text-sm text-muted-foreground">{{ value.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-3xl px-4 py-16 text-center">
                <h2 class="font-display text-2xl font-semibold text-foreground sm:text-3xl">
                    Kennismaken?
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-muted-foreground">
                    Voer uw kenteken in voor een directe berekening, of neem contact op voor een vrijblijvend gesprek.
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
                        href="/contact"
                        class="inline-flex items-center gap-2 rounded-xl border border-border px-6 py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                    >
                        Contact opnemen
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
