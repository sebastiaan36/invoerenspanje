<script setup lang="ts">
import KentekenLookup from '@/components/KentekenLookup.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <PublicLayout
        title="BPM-calculator — Bereken uw BPM-teruggave"
        description="Voer uw kenteken in en zie direct hoeveel BPM-teruggave u kunt verwachten bij export van uw auto naar Spanje."
    >
        <section class="bg-background">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24">
                <span class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground">
                    Gratis berekening
                </span>
                <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-5xl">
                    Hoeveel <span class="text-accent">BPM</span> krijgt u terug?
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-muted-foreground">
                    Voer uw kenteken in en bereken direct de indicatieve BPM-teruggave bij export naar Spanje. De berekening gebruikt de forfaitaire afschrijvingstabel op basis van de RDW-gegevens.
                </p>

                <div class="mx-auto mt-10 max-w-3xl text-left">
                    <KentekenLookup :bpm-only="true" />
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
