<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Check } from 'lucide-vue-next';
import ContactStrip from '@/components/ContactStrip.vue';
import FaqAccordion from '@/components/FaqAccordion.vue';
import type { FaqItem } from '@/components/FaqAccordion.vue';
import KentekenLookup from '@/components/KentekenLookup.vue';
import ReviewsSection from '@/components/ReviewsSection.vue';
import type { Review } from '@/components/ReviewsSection.vue';
import { scrollToQuote } from '@/composables/useScrollToQuote';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface RecentPost {
    slug: string;
    title: string;
    hero_image_url: string;
    hero_image_srcset: string | null;
}

withDefaults(
    defineProps<{
        canRegister?: boolean;
        faqItems?: FaqItem[];
        contentHtml?: string;
        reviews?: Review[];
        recentPosts?: RecentPost[];
    }>(),
    {
        canRegister: true,
        faqItems: () => [],
        contentHtml: '',
        reviews: () => [],
        recentPosts: () => [],
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

                <div id="kentekencheck" class="mt-10 scroll-mt-24 text-left">
                    <KentekenLookup />
                </div>
            </div>
        </section>

        <!-- Twee sporen, één team -->
        <section
            class="border-t border-border bg-background"
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

        <ReviewsSection :reviews="reviews" />

        <!-- SEO content -->
        <section class="border-t border-border bg-secondary/30">
            <div class="container mx-auto max-w-2xl px-4 py-16 md:py-20">
                <div
                    class="prose prose-lg max-w-none prose-headings:font-display prose-headings:text-foreground prose-h2:mt-12 prose-h2:text-2xl prose-h3:mt-8 prose-h3:text-xl prose-p:text-foreground prose-a:font-medium prose-a:text-primary prose-a:underline prose-a:underline-offset-4 hover:prose-a:text-primary/80 prose-strong:text-foreground"
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

                <template v-if="recentPosts.length">
                    <h2
                        class="mt-14 font-display text-2xl font-semibold text-balance text-foreground"
                    >
                        Meer lezen over het importproces
                    </h2>
                    <ul class="mt-6 grid gap-4 sm:grid-cols-3">
                        <li v-for="post in recentPosts" :key="post.slug">
                            <Link
                                :href="`/blog/${post.slug}`"
                                class="group flex h-full flex-col overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-shadow hover:shadow-md focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                            >
                                <div
                                    class="aspect-[16/9] w-full overflow-hidden bg-muted"
                                >
                                    <img
                                        :src="post.hero_image_url"
                                        :srcset="
                                            post.hero_image_srcset ?? undefined
                                        "
                                        sizes="(min-width: 640px) 220px, 100vw"
                                        alt=""
                                        loading="lazy"
                                        class="size-full object-cover"
                                    />
                                </div>
                                <p
                                    class="p-4 text-base leading-snug font-medium text-foreground group-hover:text-primary"
                                >
                                    {{ post.title }}
                                </p>
                            </Link>
                        </li>
                    </ul>
                    <p class="mt-4">
                        <Link
                            href="/blog"
                            class="inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Alle artikelen
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </p>
                </template>
            </div>
        </section>

        <!-- Closing CTA: the page is long, so offer the primary action again -->
        <section
            class="border-t border-border bg-background"
            aria-labelledby="closing-cta-title"
        >
            <div
                class="container mx-auto max-w-3xl px-4 py-16 text-center md:py-20"
            >
                <h2
                    id="closing-cta-title"
                    class="font-display text-3xl font-semibold text-balance text-foreground md:text-4xl"
                >
                    Wat kost het voor uw auto?
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-muted-foreground">
                    Vul uw kenteken in en zie binnen een paar seconden de netto
                    kosten, met de BPM-teruggave al verrekend. Vrijblijvend.
                </p>
                <a
                    href="#kentekencheck"
                    class="mt-8 inline-flex min-h-12 items-center gap-2 rounded-xl bg-accent px-6 text-base font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    @click.prevent="scrollToQuote()"
                >
                    Bereken uw kosten
                    <ArrowRight class="size-5" aria-hidden="true" />
                </a>
            </div>
        </section>
    </PublicLayout>
</template>
