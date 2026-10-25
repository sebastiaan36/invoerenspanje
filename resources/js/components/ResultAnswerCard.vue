<script setup lang="ts">
import { ArrowDown } from 'lucide-vue-next';
import { computed } from 'vue';
import type { ServicePackage } from '@/components/PackageSelector.vue';

const props = defineProps<{
    kenteken: string;
    vehicleSummary: string;
    selectedPackage: ServicePackage;
    importTotalEur: number;
    bpmAvailable: boolean;
    bpmEligible: boolean;
    bpmRestEur: number;
    bpmIneligibleReason: string | null;
}>();

const emit = defineEmits<{
    (e: 'request-quote'): void;
    (e: 'compare-packages'): void;
}>();

const euroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

function formatEuro(value: number): string {
    return euroFormatter.format(value);
}

const bpmBack = computed(() =>
    props.bpmAvailable && props.bpmEligible ? props.bpmRestEur : 0,
);
const youPay = computed(
    () => props.selectedPackage.price_eur + props.importTotalEur,
);
const net = computed(() => youPay.value - bpmBack.value);
const keepsMoney = computed(() => net.value < 0);

const formattedPlate = computed(() => {
    const plate = props.kenteken.replace(/[\s-]+/g, '').toUpperCase();

    // Dutch plates group into three parts; split on letter/digit changes
    // and fall back to the raw value when that yields an odd shape.
    const parts = plate.match(/[A-Z]+|[0-9]+/g) ?? [plate];

    return parts.length === 3 ? parts.join('-') : plate;
});
</script>

<template>
    <section
        class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8"
        aria-labelledby="answer-title"
    >
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <span
                class="inline-flex items-stretch overflow-hidden rounded-md border border-border text-sm"
            >
                <span
                    class="flex items-center bg-[#003399] px-1.5 text-[10px] font-bold text-white"
                    aria-hidden="true"
                    >NL</span
                >
                <span
                    class="bg-plate px-2 py-0.5 font-display tracking-wider text-plate-foreground"
                    >{{ formattedPlate }}</span
                >
            </span>
            <span class="text-sm text-muted-foreground">{{
                vehicleSummary
            }}</span>
        </div>

        <div
            class="mt-6 grid gap-8 md:grid-cols-[1fr_minmax(0,22rem)] md:gap-10"
        >
            <div>
                <h2
                    id="answer-title"
                    class="font-display text-xl font-semibold text-foreground"
                >
                    {{
                        keepsMoney
                            ? 'Wat u netto overhoudt'
                            : 'Wat het u netto kost'
                    }}
                </h2>
                <p
                    class="mt-2 font-display text-5xl font-semibold tabular-nums sm:text-6xl"
                    :class="keepsMoney ? 'text-success' : 'text-foreground'"
                >
                    {{ formatEuro(Math.abs(net)) }}
                </p>
                <p class="mt-3 max-w-md text-base text-muted-foreground">
                    Met pakket
                    <span class="font-medium text-foreground">{{
                        selectedPackage.name
                    }}</span
                    >, alle Spaanse kosten meegerekend<template
                        v-if="bpmBack > 0"
                        >, en de BPM-teruggave al verrekend</template
                    >.
                </p>

                <div
                    class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center"
                >
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-accent px-5 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        @click="emit('request-quote')"
                    >
                        Vraag een vrijblijvende offerte aan
                    </button>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl px-3 text-sm font-medium text-primary underline-offset-4 transition-colors hover:underline focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        @click="emit('compare-packages')"
                    >
                        Pakketten vergelijken
                        <ArrowDown class="size-4" />
                    </button>
                </div>
            </div>

            <div
                class="self-start rounded-xl bg-muted/60 p-5 text-sm tabular-nums"
            >
                <p class="font-display text-base font-semibold text-foreground">
                    U betaalt
                </p>
                <dl class="mt-2 space-y-2">
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-muted-foreground">
                            Pakket {{ selectedPackage.name }}
                        </dt>
                        <dd class="font-medium text-foreground">
                            {{ formatEuro(selectedPackage.price_eur) }}
                        </dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-muted-foreground">Kosten in Spanje</dt>
                        <dd class="font-medium text-foreground">
                            {{ formatEuro(importTotalEur) }}
                        </dd>
                    </div>
                </dl>

                <p
                    class="mt-5 font-display text-base font-semibold text-foreground"
                >
                    U krijgt terug
                </p>
                <dl
                    v-if="bpmAvailable && bpmEligible"
                    class="mt-2 flex items-baseline justify-between gap-3"
                >
                    <dt class="text-muted-foreground">BPM uit Nederland</dt>
                    <dd class="font-medium text-success">
                        + {{ formatEuro(bpmRestEur) }}
                    </dd>
                </dl>
                <p v-else-if="bpmAvailable" class="mt-2 text-muted-foreground">
                    Geen BPM-teruggave voor deze auto<template
                        v-if="bpmIneligibleReason"
                        >: {{ bpmIneligibleReason.toLowerCase() }}</template
                    >.
                </p>
                <p v-else class="mt-2 text-muted-foreground">
                    De BPM kon niet automatisch worden berekend. Wij rekenen het
                    voor u na in de offerte.
                </p>

                <dl
                    class="mt-5 flex items-baseline justify-between gap-3 border-t border-border pt-4"
                >
                    <dt class="font-semibold text-foreground">
                        {{ keepsMoney ? 'Netto over' : 'Netto kosten' }}
                    </dt>
                    <dd
                        class="font-display text-lg font-semibold"
                        :class="keepsMoney ? 'text-success' : 'text-foreground'"
                    >
                        {{ formatEuro(Math.abs(net)) }}
                    </dd>
                </dl>
            </div>
        </div>
    </section>
</template>
