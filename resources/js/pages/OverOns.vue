<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Check } from 'lucide-vue-next';
import ContactStrip from '@/components/ContactStrip.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

const team = [
    {
        name: 'Maikel',
        photo: '/images/maikel.jpeg',
        alt: 'Maikel, autospecialist',
        company: 'Maikel Mobility',
        role: 'Autospecialist, regelt de Spaanse kant van het traject',
        bio: 'Maikel is de autospecialist achter het importtraject. Hij studeerde HBO Automotive aan de IVA, werkt met erkende RDW-exportpartners en kent de procedures bij de DGT (de Spaanse RDW). Van Ficha Técnica (Spaans technisch voertuigrapport) tot ITV (de Spaanse APK-keuring): Maikel regelt het.',
        highlights: [
            'IVA-gecertificeerd: HBO Automotive aan de IVA',
            'Werkt met erkende RDW-exportpartners',
            'Kent de procedures bij de DGT (de Spaanse RDW)',
        ],
    },
    {
        name: 'Sebastiaan',
        photo: '/images/sebastiaan.jpeg',
        alt: 'Sebastiaan, online aanspreekpunt',
        company: 'Been & van Dam',
        role: 'Uw aanspreekpunt online',
        bio: 'Sebastiaan is uw aanspreekpunt online. Hij beantwoordt uw vragen over de berekening en de offerte, en houdt uw dossier bij, zodat u steeds weet hoe het met uw auto staat.',
        highlights: [
            'Beantwoordt uw vragen over berekening en offerte',
            'Houdt uw dossier en de berekening voor u bij',
        ],
    },
];

const values = [
    {
        title: 'Alles uit één hand, van Nederland tot Spanje',
        body: 'De BPM-teruggave en export in Nederland, en de inschrijving op een Spaans kenteken (matriculación) en de keuring (ITV) in Spanje: één team regelt beide kanten. U hoeft zelf geen Nederlandse exporteur en Spaanse gestoría (administratiekantoor) te combineren.',
    },
    {
        title: 'Nederlandstalig, met vaste prijzen',
        body: 'U spreekt met ons gewoon Nederlands, ook over de Spaanse kant. U kiest vooraf een pakket met een vaste prijs, zodat u weet waar u aan toe bent voordat u akkoord gaat.',
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
                    Over ons
                </h1>
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground"
                >
                    autoinvoerenspanje.nl is een samenwerking tussen een
                    IVA-gecertificeerde autospecialist en een online marketeer.
                    Samen maken wij het importtraject eenvoudig, transparant en
                    volledig Nederlandstalig.
                </p>
            </div>
        </section>

        <!-- Missie -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-3xl px-4 py-16 text-center">
                <h2
                    class="font-display text-2xl font-semibold text-foreground sm:text-3xl"
                >
                    Onze missie
                </h2>
                <p class="mx-auto mt-5 max-w-2xl text-muted-foreground">
                    Veel Nederlanders wonen of verblijven langdurig aan de Costa
                    del Sol. Zij willen hun vertrouwde auto meenemen naar
                    Spanje, maar lopen vast op bureaucratie, taalbarrières en
                    een ondoorzichtig systeem. Wij zetten dat recht: één
                    aanspreekpunt, vaste prijs, alles geregeld.
                </p>
            </div>
        </section>

        <!-- Team -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="text-center">
                    <h2
                        class="font-display text-2xl font-semibold text-foreground sm:text-3xl"
                    >
                        Het team
                    </h2>
                    <p class="mx-auto mt-3 max-w-xl text-muted-foreground">
                        Twee mensen met elk hun eigen vakgebied. Samen zorgen
                        zij voor het complete plaatje.
                    </p>
                </div>

                <div class="mt-12 grid gap-8 lg:grid-cols-2">
                    <article
                        v-for="member in team"
                        :key="member.name"
                        class="flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
                    >
                        <div
                            class="h-64 w-full overflow-hidden bg-muted sm:h-80 lg:h-96"
                        >
                            <img
                                :src="member.photo"
                                :alt="member.alt"
                                width="800"
                                height="800"
                                loading="lazy"
                                class="h-full w-full object-cover object-top"
                            />
                        </div>

                        <div class="flex flex-col p-6 sm:p-8">
                            <div>
                                <h3
                                    class="font-display text-xl font-semibold text-foreground"
                                >
                                    {{ member.name }}
                                </h3>
                                <p class="text-sm font-medium text-foreground">
                                    {{ member.company }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ member.role }}
                                </p>
                            </div>

                            <p class="mt-4 leading-relaxed text-foreground/80">
                                {{ member.bio }}
                            </p>

                            <ul class="mt-5 space-y-2">
                                <li
                                    v-for="highlight in member.highlights"
                                    :key="highlight"
                                    class="flex items-start gap-2.5"
                                >
                                    <Check
                                        class="mt-1 size-4 shrink-0 stroke-[3] text-success"
                                        aria-hidden="true"
                                    />
                                    <span class="text-foreground">{{
                                        highlight
                                    }}</span>
                                </li>
                            </ul>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Waarden -->
        <section class="border-t border-border bg-muted/40">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <h2
                    class="text-center font-display text-2xl font-semibold text-foreground sm:text-3xl"
                >
                    Waar wij voor staan
                </h2>

                <div class="mt-10 grid gap-10 md:grid-cols-2 md:gap-12">
                    <div
                        v-for="value in values"
                        :key="value.title"
                        class="border-t border-border pt-6"
                    >
                        <h3
                            class="font-display text-xl font-semibold text-foreground"
                        >
                            {{ value.title }}
                        </h3>
                        <p class="mt-3 leading-relaxed text-muted-foreground">
                            {{ value.body }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16">
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="font-display text-2xl font-semibold text-foreground sm:text-3xl"
                    >
                        Kennismaken?
                    </h2>
                    <p class="mx-auto mt-4 max-w-lg text-muted-foreground">
                        Vul uw kenteken in voor een directe berekening, of neem
                        contact op voor een vrijblijvend gesprek.
                    </p>
                    <div class="mt-6 flex justify-center">
                        <Link
                            href="/#offerte"
                            class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-accent px-6 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        >
                            Bereken mijn kosten
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                </div>

                <ContactStrip
                    class="mt-12"
                    title="Liever eerst even praten?"
                    body="Bel of WhatsApp ons. U spreekt direct met Maikel of Sebastiaan, in het Nederlands."
                    secondary-label="Contactformulier"
                    secondary-href="/contact"
                />
            </div>
        </section>
    </PublicLayout>
</template>
