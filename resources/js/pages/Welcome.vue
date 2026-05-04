<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Check } from 'lucide-vue-next';

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
            <div class="container mx-auto grid gap-12 px-4 py-20 md:grid-cols-2 md:items-center md:py-28">
                <div>
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
                    <p class="mt-5 max-w-lg text-lg text-muted-foreground">
                        Van papierwerk tot ITV en BPM-teruggave — wij regelen
                        het complete traject. Voer hieronder je kenteken in en
                        zie direct wat je auto je oplevert.
                    </p>

                    <div
                        class="mt-8 rounded-2xl border border-border bg-card p-2 shadow-sm"
                    >
                        <div
                            class="flex items-center gap-3 rounded-xl bg-muted px-4 py-4 text-sm text-muted-foreground"
                        >
                            <span
                                class="rounded-md bg-primary px-2 py-1 font-semibold text-primary-foreground"
                            >
                                NL
                            </span>
                            <span class="italic">
                                Kenteken-lookup komt in Fase 2 — placeholder.
                            </span>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-muted-foreground"
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

                <div class="relative">
                    <div
                        class="aspect-[4/5] w-full rounded-2xl bg-gradient-to-br from-primary via-primary-light to-accent shadow-lg"
                    ></div>
                    <div
                        class="absolute -bottom-6 -left-6 max-w-[15rem] rounded-2xl border border-border bg-card p-5 shadow-lg"
                    >
                        <div class="text-xs font-medium text-muted-foreground">
                            Indicatie BPM-teruggave
                        </div>
                        <div class="mt-1 font-display text-3xl font-semibold text-accent">
                            € 2.840
                        </div>
                        <div class="mt-1 text-xs text-muted-foreground">
                            Voorbeeld op basis van een Volkswagen Golf 2019
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
