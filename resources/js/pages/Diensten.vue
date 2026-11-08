<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import AccentHeading from '@/components/AccentHeading.vue';
import ContactStrip from '@/components/ContactStrip.vue';
import { usePageSeo } from '@/composables/usePageSeo';
import PublicLayout from '@/layouts/PublicLayout.vue';

withDefaults(defineProps<{ contentHtml?: string }>(), { contentHtml: '' });

const seo = usePageSeo();

const quoteHref = '/#offerte';
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

        <!-- Het dossier (Nederland en Spanje) en aanvullende diensten -->
        <!-- eslint-disable-next-line vue/no-v-html -- trusted server-rendered copy from resources/views/content/diensten.blade.php -->
        <div v-html="contentHtml" />

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
                        Vul uw Nederlandse kenteken in en zie direct de
                        BPM-teruggave en de kosten in Spanje. Daarna vraagt u
                        met één formulier een offerte aan.
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
