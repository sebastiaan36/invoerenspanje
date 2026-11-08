<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import AccentHeading from '@/components/AccentHeading.vue';
import ContactStrip from '@/components/ContactStrip.vue';
import { usePageSeo } from '@/composables/usePageSeo';
import PublicLayout from '@/layouts/PublicLayout.vue';

withDefaults(defineProps<{ contentHtml?: string }>(), { contentHtml: '' });

const seo = usePageSeo();
</script>

<template>
    <PublicLayout>
        <!-- Hero -->
        <section class="bg-background">
            <div
                class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24"
            >
                <AccentHeading
                    :text="seo.h1 ?? ''"
                    :accent="seo.h1_accent"
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-5xl"
                />
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground"
                >
                    {{ seo.intro }}
                </p>
            </div>
        </section>

        <!-- Missie, team en waarden -->
        <!-- eslint-disable-next-line vue/no-v-html -- trusted server-rendered copy from resources/views/content/over-ons.blade.php -->
        <div v-html="contentHtml" />

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
                        Vul uw Nederlandse kenteken in voor een directe
                        berekening, of neem contact op voor een vrijblijvend
                        gesprek.
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
