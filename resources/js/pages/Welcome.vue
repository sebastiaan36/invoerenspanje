<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import KentekenLookup from '@/components/KentekenLookup.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

withDefaults(
    defineProps<{
        canRegister?: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <PublicLayout
        title="Auto op Spaans kenteken — voor Nederlanders in Spanje"
        description="Wij regelen het hele traject om je Nederlandse auto op Spaans kenteken te zetten — van papierwerk en BPM-teruggave tot de ITV. Voor Nederlanders aan de Costa del Sol."
    >
        <section class="relative overflow-hidden bg-background">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-24">
                <span
                    class="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground"
                >
                    Voor Nederlanders aan de Costa del Sol
                </span>
                <h1
                    class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-6xl"
                >
                    Je auto op
                    <span class="text-accent">Spaans kenteken</span>,
                    zonder gedoe.
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-muted-foreground">
                    Van papierwerk tot ITV en BPM-teruggave — wij regelen het
                    complete traject. Voer hieronder je kenteken in en zie direct
                    wat je auto je oplevert.
                </p>

                <div class="mx-auto mt-10 max-w-3xl text-left">
                    <KentekenLookup />
                </div>

                <div
                    class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-muted-foreground"
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
            </div>
        </section>
    </PublicLayout>
</template>
