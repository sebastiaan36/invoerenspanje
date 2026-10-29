<script setup lang="ts">
import ContactStrip from '@/components/ContactStrip.vue';
import KentekenLookup from '@/components/KentekenLookup.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

withDefaults(defineProps<{ contentHtml?: string }>(), { contentHtml: '' });
</script>

<template>
    <PublicLayout>
        <section class="bg-background">
            <div
                class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24"
            >
                <h1
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-5xl"
                >
                    Bereken uw rest-<span class="text-accent">BPM</span> bij
                    export
                </h1>
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground"
                >
                    Voer uw kenteken in en zie gratis hoeveel BPM u ongeveer
                    terugkrijgt bij export naar Spanje. Wij rekenen met de BPM
                    die de RDW voor uw auto heeft geregistreerd en de vaste
                    afschrijvingstabel van de Belastingdienst.
                </p>

                <div class="mx-auto mt-10 max-w-3xl text-left">
                    <KentekenLookup :bpm-only="true" />
                </div>
            </div>
        </section>

        <!-- SEO content -->
        <section class="border-t border-border bg-secondary/30">
            <div class="container mx-auto max-w-2xl px-4 py-16 md:py-20">
                <div
                    class="prose prose-lg max-w-none prose-headings:font-display prose-headings:text-balance prose-headings:text-foreground prose-h2:mt-12 prose-h2:text-2xl prose-p:text-foreground prose-a:font-medium prose-a:text-primary prose-a:underline-offset-4 hover:prose-a:text-primary/80 prose-strong:text-foreground"
                >
                    <!-- eslint-disable-next-line vue/no-v-html -- trusted server-rendered copy from resources/views/content/bpm-calculator.blade.php -->
                    <div v-html="contentHtml" />
                </div>
            </div>
        </section>

        <section class="border-t border-border bg-background">
            <div class="container mx-auto max-w-5xl px-4 py-16 md:py-20">
                <ContactStrip
                    body="Vragen over uw bedrag? Maikel rekent het graag met u na."
                    secondary-label="Vraag een offerte aan"
                    secondary-href="/#offerte"
                />
            </div>
        </section>
    </PublicLayout>
</template>
