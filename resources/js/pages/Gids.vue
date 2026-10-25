<script setup lang="ts">
import ContactStrip from '@/components/ContactStrip.vue';
import FaqAccordion from '@/components/FaqAccordion.vue';
import type { FaqItem } from '@/components/FaqAccordion.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

withDefaults(
    defineProps<{
        contentHtml?: string;
        faqItems?: FaqItem[];
        updatedLabel?: string;
    }>(),
    {
        contentHtml: '',
        faqItems: () => [],
        updatedLabel: '',
    },
);
</script>

<template>
    <PublicLayout>
        <section class="bg-background">
            <div class="container mx-auto max-w-3xl px-4 pt-16 pb-10 md:pt-24">
                <h1
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-5xl"
                >
                    Auto invoeren in Spanje: de complete gids
                </h1>
                <p
                    v-if="updatedLabel"
                    class="mt-4 text-sm text-muted-foreground"
                >
                    {{ updatedLabel }}
                </p>
            </div>
        </section>

        <section class="bg-background">
            <div class="container mx-auto max-w-3xl px-4 pb-16">
                <article
                    class="prose prose-lg max-w-[68ch] prose-headings:font-display prose-headings:text-balance prose-headings:text-foreground prose-h2:mt-12 prose-h2:text-2xl prose-p:text-foreground prose-a:font-medium prose-a:text-primary prose-a:underline-offset-4 hover:prose-a:text-primary/80 prose-strong:text-foreground prose-li:text-foreground prose-table:text-base prose-th:text-left"
                >
                    <!-- eslint-disable-next-line vue/no-v-html -- trusted server-rendered copy from resources/views/content/gids.blade.php -->
                    <div v-html="contentHtml" />
                </article>

                <div class="mt-12 max-w-[68ch]">
                    <a
                        href="/#offerte"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-accent px-5 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        Bereken wat het voor uw auto kost
                    </a>
                </div>

                <h2
                    v-if="faqItems.length"
                    class="mt-16 font-display text-2xl font-semibold text-balance text-foreground"
                >
                    Veelgestelde vragen
                </h2>
                <FaqAccordion
                    v-if="faqItems.length"
                    :items="faqItems"
                    class="mt-6 max-w-[68ch]"
                />
            </div>
        </section>

        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16 md:py-20">
                <ContactStrip
                    body="Vragen over uw situatie? Maikel denkt graag met u mee."
                    secondary-label="Vraag een offerte aan"
                    secondary-href="/#offerte"
                />
            </div>
        </section>
    </PublicLayout>
</template>
