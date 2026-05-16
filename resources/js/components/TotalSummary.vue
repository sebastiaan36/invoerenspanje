<script setup lang="ts">
import { TrendingUp, TrendingDown } from 'lucide-vue-next';
import { computed } from 'vue';
import type { ServicePackage } from '@/components/PackageSelector.vue';

const props = defineProps<{
    selectedPackage: ServicePackage;
    importTotalEur: number;
    bpmEligible: boolean;
    bpmRestEur: number;
}>();

const euroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

const signedEuroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
    signDisplay: 'always',
});

function formatEuro(value: number): string {
    return euroFormatter.format(value);
}

const packagePrice = computed(() => props.selectedPackage.price_eur);
const subtotaal = computed(() => packagePrice.value + props.importTotalEur);
const bpmBack = computed(() => (props.bpmEligible ? props.bpmRestEur : 0));
const nettoInvestering = computed(() => subtotaal.value - bpmBack.value);
const isOpbrengst = computed(() => nettoInvestering.value < 0);
const heroAmount = computed(() => Math.abs(nettoInvestering.value));
</script>

<template>
    <section
        class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8"
        aria-labelledby="totaaloverzicht-title"
    >
        <header>
            <div class="text-xs font-semibold uppercase tracking-wider text-accent">
                Uw keuze samengevat
            </div>
            <h3
                id="totaaloverzicht-title"
                class="mt-1 font-display text-2xl font-semibold text-foreground"
            >
                Totaaloverzicht
            </h3>
        </header>

        <dl class="mt-6 space-y-3 text-sm">
            <div class="flex items-baseline justify-between gap-3">
                <dt class="text-muted-foreground">
                    Pakket
                    <span class="font-medium text-foreground">{{ selectedPackage.name }}</span>
                </dt>
                <dd class="text-right font-medium tabular-nums">
                    {{ formatEuro(packagePrice) }}
                </dd>
            </div>

            <div class="flex items-baseline justify-between gap-3">
                <dt class="text-muted-foreground">Spaanse importkosten</dt>
                <dd class="text-right font-medium tabular-nums">
                    + {{ formatEuro(importTotalEur) }}
                </dd>
            </div>

            <div class="border-t border-border pt-3">
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="font-display text-base font-semibold text-foreground">
                        Totaalprijs
                    </dt>
                    <dd class="text-right font-display text-lg font-semibold tabular-nums text-foreground">
                        {{ formatEuro(subtotaal) }}
                    </dd>
                </div>
            </div>
        </dl>

        <div class="mt-6">
            <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Tegenover
            </div>
            <dl class="mt-3 space-y-3 text-sm">
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-muted-foreground">
                        BPM-teruggave Nederland
                        <span v-if="!bpmEligible" class="text-xs">(niet van toepassing)</span>
                    </dt>
                    <dd class="text-right font-medium tabular-nums text-success">
                        − {{ formatEuro(bpmBack) }}
                    </dd>
                </div>
            </dl>
        </div>

        <div
            class="mt-6 flex flex-col gap-3 border-t-2 border-border pt-5 sm:flex-row sm:items-end sm:justify-between sm:gap-4"
        >
            <div>
                <div
                    class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider"
                    :class="isOpbrengst ? 'text-success' : 'text-accent'"
                >
                    <TrendingUp v-if="isOpbrengst" class="size-4" />
                    <TrendingDown v-else class="size-4" />
                    {{ isOpbrengst ? 'Netto-opbrengst' : 'Netto-investering' }}
                </div>
                <p class="mt-1 max-w-xs text-xs text-muted-foreground">
                    <span v-if="isOpbrengst">
                        De BPM-teruggave is hoger dan de totaalprijs — u houdt geld over.
                    </span>
                    <span v-else>
                        Wat u netto investeert in het complete traject naar Spaans kenteken.
                    </span>
                </p>
            </div>
            <div
                class="font-display text-3xl font-semibold tabular-nums sm:text-4xl"
                :class="isOpbrengst ? 'text-success' : 'text-accent'"
            >
                {{ isOpbrengst ? signedEuroFormatter.format(heroAmount) : formatEuro(heroAmount) }}
            </div>
        </div>
    </section>
</template>
