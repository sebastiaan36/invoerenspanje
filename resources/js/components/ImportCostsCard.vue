<script setup lang="ts">
import { Info, AlertTriangle, BadgeCheck, Leaf } from 'lucide-vue-next';
import { computed } from 'vue';

interface FixedCost {
    key: string;
    label: string;
    amount_eur: number;
}

interface ImportCostsPayload {
    iedmt_eur: number;
    iedmt_rate_pct: number;
    iedmt_exempt: boolean;
    iedmt_exempt_reason: string | null;
    estimated_market_value_eur: number;
    fixed_costs: FixedCost[];
    fixed_costs_total_eur: number;
    total_eur: number;
    autonomia: string;
    notes: string[];
}

const props = defineProps<{
    importCosts: ImportCostsPayload;
    co2: number | null;
}>();

// Banner verschijnt zodra het tarief 0% is zonder dat er een exemption
// is toegepast — dat dekt zowel CO2 < 120 als CO2 = 120 (beide vallen in
// de 0%-schijf).
const isBelowThreshold = computed(
    () => props.importCosts.iedmt_rate_pct === 0 && !props.importCosts.iedmt_exempt,
);

const euroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

function formatEuro(value: number): string {
    return euroFormatter.format(value);
}
</script>

<template>
    <article class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
            Spaanse import-kosten
        </div>
        <div class="mt-1 font-display text-3xl font-semibold text-foreground">
            {{ formatEuro(importCosts.total_eur) }}
        </div>

        <div
            v-if="isBelowThreshold"
            class="mt-4 flex items-start gap-2 rounded-xl border border-success/30 bg-success/10 p-3 text-xs text-foreground"
        >
            <Leaf class="mt-0.5 size-4 shrink-0 text-success" />
            <p>
                <strong>Geen Spaanse registratiebelasting verschuldigd:</strong>
                uw auto valt onder de 120 g/km drempel.
            </p>
        </div>

        <dl class="mt-5 space-y-3 text-sm">
            <div class="flex items-baseline justify-between gap-3">
                <dt class="text-muted-foreground">
                    IEDMT
                    <span class="text-xs">({{ importCosts.iedmt_rate_pct.toFixed(2) }}%)</span>
                </dt>
                <dd class="text-right font-medium tabular-nums">
                    <span v-if="importCosts.iedmt_exempt" class="inline-flex items-center gap-1 text-success">
                        <BadgeCheck class="size-4" />
                        Vrijgesteld
                    </span>
                    <span v-else>{{ formatEuro(importCosts.iedmt_eur) }}</span>
                </dd>
            </div>
            <div
                v-if="importCosts.estimated_market_value_eur > 0"
                class="flex items-baseline justify-between gap-3 text-xs text-muted-foreground"
            >
                <dt>Geschatte marktwaarde</dt>
                <dd class="tabular-nums">{{ formatEuro(importCosts.estimated_market_value_eur) }}</dd>
            </div>

            <div class="my-2 border-t border-border"></div>

            <div
                v-for="cost in importCosts.fixed_costs"
                :key="cost.key"
                class="flex items-baseline justify-between gap-3"
            >
                <dt class="text-muted-foreground">{{ cost.label }}</dt>
                <dd class="text-right font-medium tabular-nums">{{ formatEuro(cost.amount_eur) }}</dd>
            </div>

            <div class="mt-2 flex items-baseline justify-between gap-3 border-t border-border pt-3">
                <dt class="font-semibold text-foreground">Totaal kosten Spanje</dt>
                <dd class="text-right font-display text-lg font-semibold tabular-nums text-foreground">
                    {{ formatEuro(importCosts.total_eur) }}
                </dd>
            </div>
        </dl>

        <div
            v-if="importCosts.iedmt_exempt && importCosts.iedmt_exempt_reason"
            class="mt-4 flex items-start gap-2 rounded-xl bg-success/10 p-3 text-xs text-success"
        >
            <BadgeCheck class="mt-0.5 size-4 shrink-0" />
            <p>{{ importCosts.iedmt_exempt_reason }}</p>
        </div>

        <div
            v-for="note in importCosts.notes"
            :key="note"
            class="mt-3 flex items-start gap-2 rounded-xl border border-warning/30 bg-warning/10 p-3 text-xs text-foreground"
        >
            <AlertTriangle class="mt-0.5 size-4 shrink-0 text-warning" />
            <p>{{ note }}</p>
        </div>

        <div
            class="mt-3 flex items-start gap-2 rounded-xl bg-muted p-3 text-xs text-muted-foreground"
        >
            <Info class="mt-0.5 size-4 shrink-0" />
            <p>
                Vaste kosten zijn een richtprijs op basis van onze partner-tarieven. Het
                exacte bedrag hangt af van de gekozen autonome regio en eventueel
                aanvullende werkzaamheden (specifieke homologatie, vertalingen).
            </p>
        </div>
    </article>
</template>
