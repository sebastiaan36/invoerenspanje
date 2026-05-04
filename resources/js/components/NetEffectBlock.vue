<script setup lang="ts">
import { computed } from 'vue';
import { TrendingUp, TrendingDown, MinusCircle } from 'lucide-vue-next';

const props = defineProps<{
    netEffectEur: number;
    bpmRestEur: number;
    bpmEligible: boolean;
    importTotalEur: number;
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

const tone = computed<'positive' | 'negative' | 'neutral'>(() => {
    if (props.netEffectEur > 0) return 'positive';
    if (props.netEffectEur < 0) return 'negative';
    return 'neutral';
});

const headline = computed(() => {
    if (tone.value === 'positive') return 'Je houdt naar verwachting geld over';
    if (tone.value === 'negative') return 'Verwachte netto-kosten van de export';
    return 'Verwacht netto-resultaat: gelijk gespeeld';
});

const tagline = computed(() => {
    if (tone.value === 'positive') {
        return 'De BPM-teruggave is groter dan de Spaanse import-kosten.';
    }
    if (tone.value === 'negative' && !props.bpmEligible) {
        return 'Voor deze auto is geen BPM-teruggave mogelijk; de Spaanse import-kosten resteren.';
    }
    if (tone.value === 'negative') {
        return 'De Spaanse import-kosten zijn hoger dan de BPM-teruggave.';
    }
    return 'BPM-teruggave en Spaanse kosten vallen tegen elkaar weg.';
});
</script>

<template>
    <section
        class="rounded-2xl bg-primary p-7 text-primary-foreground shadow-lg"
        :class="{
            'ring-2 ring-success/40': tone === 'positive',
            'ring-2 ring-destructive/40': tone === 'negative',
        }"
    >
        <div class="flex items-start justify-between gap-6">
            <div>
                <div class="text-xs font-medium uppercase tracking-wider text-primary-foreground/70">
                    Netto-resultaat bij export naar Spanje
                </div>
                <h2 class="mt-1 font-display text-xl font-semibold text-primary-foreground">
                    {{ headline }}
                </h2>
            </div>
            <div
                class="flex size-12 shrink-0 items-center justify-center rounded-full"
                :class="{
                    'bg-success/20 text-success': tone === 'positive',
                    'bg-destructive/20 text-destructive': tone === 'negative',
                    'bg-primary-foreground/10 text-primary-foreground/60': tone === 'neutral',
                }"
            >
                <TrendingUp v-if="tone === 'positive'" class="size-6" />
                <TrendingDown v-else-if="tone === 'negative'" class="size-6" />
                <MinusCircle v-else class="size-6" />
            </div>
        </div>

        <div
            class="mt-6 font-display text-5xl font-semibold tabular-nums md:text-6xl"
            :class="{
                'text-success': tone === 'positive',
                'text-accent': tone === 'negative',
                'text-primary-foreground/70': tone === 'neutral',
            }"
        >
            {{ signedEuroFormatter.format(netEffectEur) }}
        </div>

        <p class="mt-3 text-sm text-primary-foreground/80">{{ tagline }}</p>

        <div
            class="mt-6 grid gap-3 rounded-xl bg-primary-foreground/10 p-4 text-sm text-primary-foreground/90 sm:grid-cols-3"
        >
            <div>
                <div class="text-xs uppercase tracking-wider text-primary-foreground/60">
                    BPM-teruggave NL
                </div>
                <div class="mt-1 font-display text-xl tabular-nums text-success">
                    + {{ euroFormatter.format(bpmRestEur) }}
                </div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wider text-primary-foreground/60">
                    Kosten Spanje
                </div>
                <div class="mt-1 font-display text-xl tabular-nums text-accent">
                    − {{ euroFormatter.format(importTotalEur) }}
                </div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wider text-primary-foreground/60">
                    Netto
                </div>
                <div
                    class="mt-1 font-display text-xl tabular-nums"
                    :class="{
                        'text-success': tone === 'positive',
                        'text-accent': tone === 'negative',
                        'text-primary-foreground/70': tone === 'neutral',
                    }"
                >
                    {{ signedEuroFormatter.format(netEffectEur) }}
                </div>
            </div>
        </div>
    </section>
</template>
