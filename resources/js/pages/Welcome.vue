<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Check } from 'lucide-vue-next';
import ContactStrip from '@/components/ContactStrip.vue';
import FaqAccordion from '@/components/FaqAccordion.vue';
import type { FaqItem } from '@/components/FaqAccordion.vue';
import KentekenLookup from '@/components/KentekenLookup.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

withDefaults(
    defineProps<{
        canRegister?: boolean;
        faqItems?: FaqItem[];
        contentHtml?: string;
    }>(),
    {
        canRegister: true,
        faqItems: () => [],
        contentHtml: '',
    },
);

const lanes = [
    {
        country: 'Nederland',
        authorities: 'RDW en Belastingdienst',
        steps: [
            {
                title: 'Exportmelding bij de RDW',
                body: 'De auto wordt afgemeld met de status export.',
            },
            {
                title: 'Exportkenteken voor de rit',
                body: 'Veertien dagen geldig om naar Spanje te rijden.',
            },
            {
                title: 'BPM-teruggave',
                body: 'Wij vragen de rest-BPM terug bij de Belastingdienst.',
            },
        ],
    },
    {
        country: 'Spanje',
        authorities: 'ITV en DGT',
        steps: [
            {
                title: 'ITV-keuring',
                body: 'De Spaanse APK bij een erkend keuringsstation.',
            },
            {
                title: 'Registratiebelasting of vrijstelling',
                body: 'IEDMT op basis van CO₂-uitstoot, of vrijstelling bij verhuizing.',
            },
            {
                title: 'Spaans kenteken',
                body: 'Kentekenbewijs en platen via de DGT.',
            },
        ],
    },
];
</script>

<template>
    <PublicLayout>
        <section class="relative overflow-hidden bg-background">
            <div
                class="container mx-auto max-w-6xl px-4 py-16 text-center md:py-24"
            >
                <h1
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-6xl"
                >
                    Uw auto op
                    <span class="text-accent">Spaans kenteken</span>, zonder
                    gedoe.
                </h1>
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground"
                >
                    Voor Nederlanders aan de Costa del Sol. Wij regelen het hele
                    traject, van papierwerk tot ITV en BPM-teruggave. Vul uw
                    kenteken in en zie direct wat het u netto kost, met de
                    BPM-teruggave al verrekend.
                </p>

                <div
                    class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-muted-foreground"
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

                <div class="mt-10 text-left">
                    <KentekenLookup />
                </div>
            </div>
        </section>

        <!-- Twee sporen, één team -->
        <section
            class="border-t border-border bg-card"
            aria-labelledby="two-lanes-title"
        >
            <div class="container mx-auto max-w-5xl px-4 py-16 md:py-24">
                <h2
                    id="two-lanes-title"
                    class="max-w-2xl font-display text-3xl font-semibold text-balance text-foreground md:text-4xl"
                >
                    Nederland én Spanje, geregeld door één team
                </h2>
                <p class="mt-4 max-w-2xl text-lg text-muted-foreground">
                    Uw auto doorloopt twee trajecten tegelijk. De timing tussen
                    beide bepaalt of u recht houdt op BPM-teruggave. Wij stemmen
                    ze op elkaar af, zodat u maar één aanspreekpunt heeft.
                </p>

                <div class="relative mt-12 grid gap-10 md:grid-cols-2 md:gap-0">
                    <div
                        v-for="(lane, laneIndex) in lanes"
                        :key="lane.country"
                        :class="
                            laneIndex === 0
                                ? 'md:border-r md:border-border md:pr-12'
                                : 'md:pl-12'
                        "
                    >
                        <h3
                            class="flex items-baseline gap-3 font-display text-2xl font-semibold text-foreground"
                        >
                            {{ lane.country }}
                            <span
                                class="font-sans text-sm font-normal text-muted-foreground"
                                >{{ lane.authorities }}</span
                            >
                        </h3>
                        <ol class="mt-6 space-y-5">
                            <li
                                v-for="step in lane.steps"
                                :key="step.title"
                                class="flex gap-4"
                            >
                                <span
                                    class="mt-2 size-2 shrink-0 rounded-full"
                                    :class="
                                        laneIndex === 0
                                            ? 'bg-primary'
                                            : 'bg-accent'
                                    "
                                    aria-hidden="true"
                                />
                                <div>
                                    <p class="font-medium text-foreground">
                                        {{ step.title }}
                                    </p>
                                    <p class="mt-1 text-muted-foreground">
                                        {{ step.body }}
                                    </p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                <ContactStrip class="mt-16" />
            </div>
        </section>

        <!-- SEO content -->
        <section class="border-t border-border bg-secondary/30">
            <div class="container mx-auto max-w-2xl px-4 py-16 md:py-20">
                <div
                    class="prose prose-lg max-w-none prose-headings:font-display prose-headings:text-foreground prose-h2:mt-12 prose-h2:text-2xl prose-h3:mt-8 prose-h3:text-xl prose-p:text-foreground prose-a:font-medium prose-a:text-accent hover:prose-a:underline prose-strong:text-foreground"
                >
                    <!-- eslint-disable-next-line vue/no-v-html -- trusted server-rendered copy from resources/views/content/home.blade.php -->
                    <div v-html="contentHtml" />
                </div>

                <h2
                    class="mt-12 font-display text-2xl font-semibold text-balance text-foreground"
                >
                    Veelgestelde vragen over auto invoeren in Spanje
                </h2>
                <FaqAccordion :items="faqItems" class="mt-6" />
                <p class="mt-6">
                    <Link
                        href="/veelgestelde-vragen"
                        class="inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-primary underline-offset-4 hover:underline"
                    >
                        Alle veelgestelde vragen
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </p>

                <div
                    class="prose prose-lg max-w-none prose-headings:font-display prose-headings:text-foreground prose-h2:mt-12 prose-h2:text-2xl prose-h3:mt-8 prose-h3:text-xl prose-p:text-foreground prose-a:font-medium prose-a:text-accent hover:prose-a:underline prose-strong:text-foreground"
                >
                    <p
                        class="mt-10 rounded-xl border border-border bg-card p-5 text-base text-muted-foreground"
                    >
                        Wilt u meer lezen over het importproces? In onze
                        <a href="/blog">blogreeks</a> behandelen we onderwerpen
                        als BPM-teruggave, het exportkenteken aanvragen, de
                        ITV-keuring en de Spaanse invoerbelasting — stap voor
                        stap uitgelegd.
                    </p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
