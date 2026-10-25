<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import ContactStrip from '@/components/ContactStrip.vue';
import FaqAccordion from '@/components/FaqAccordion.vue';
import type { FaqItem } from '@/components/FaqAccordion.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface FaqCategory {
    id: string;
    title: string;
    items: FaqItem[];
}

defineProps<{
    categories: FaqCategory[];
}>();
</script>

<template>
    <PublicLayout>
        <!-- Header -->
        <section class="bg-background">
            <div
                class="container mx-auto max-w-3xl px-4 py-16 text-center md:py-24"
            >
                <h1
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-5xl"
                >
                    Veelgestelde <span class="text-accent">vragen</span>
                </h1>
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground"
                >
                    Antwoorden op de meest gestelde vragen over het importeren
                    van een auto naar Spanje: van BPM-teruggave en de
                    ITV-keuring tot documenten en kosten.
                </p>

                <nav
                    aria-label="Onderwerpen"
                    class="mt-10 flex flex-wrap justify-center gap-2"
                >
                    <a
                        v-for="category in categories"
                        :key="category.id"
                        :href="`#${category.id}`"
                        class="inline-flex min-h-11 items-center rounded-full border border-border bg-card px-4 text-sm font-medium text-foreground transition-colors hover:bg-secondary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        {{ category.title }}
                    </a>
                </nav>
            </div>
        </section>

        <!-- FAQ per categorie -->
        <section class="border-t border-border bg-secondary/30">
            <div class="container mx-auto max-w-3xl px-4 py-14 md:py-20">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="mb-12"
                >
                    <h2
                        :id="category.id"
                        class="mb-6 scroll-mt-24 font-display text-2xl font-semibold text-balance text-foreground"
                    >
                        {{ category.title }}
                    </h2>

                    <FaqAccordion :items="category.items" />
                </div>
            </div>
        </section>

        <!-- Afsluitende CTA -->
        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16 md:py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="font-display text-3xl font-semibold text-balance text-foreground"
                    >
                        Staat uw vraag er niet bij?
                    </h2>
                    <p class="mt-4 text-lg text-pretty text-muted-foreground">
                        Voer uw kenteken in en zie direct wat u terugkrijgt en
                        wat het kost. Of stel uw vraag rechtstreeks aan ons.
                    </p>
                    <Link
                        href="/#offerte"
                        class="mt-6 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-accent px-6 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        Bereken wat u terugkrijgt
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </div>

                <ContactStrip
                    class="mt-12"
                    body="Liever even overleggen? Maikel en Sebastiaan denken vrijblijvend met u mee."
                    secondary-label="Stel uw vraag"
                    secondary-href="/contact"
                />
            </div>
        </section>
    </PublicLayout>
</template>
