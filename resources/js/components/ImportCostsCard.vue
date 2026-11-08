<script setup lang="ts">
import { Info, AlertTriangle, BadgeCheck, Leaf } from 'lucide-vue-next';
import { computed } from 'vue';
import type { ImportCostsPayload } from '@/types/quote';

const props = defineProps<{
    importCosts: ImportCostsPayload;
    co2: number | null;
}>();

// Banner verschijnt zodra het tarief 0% is zonder dat er een exemption
// is toegepast — dat dekt zowel CO2 < 120 als CO2 = 120 (beide vallen in
// de 0%-schijf).
const isBelowThreshold = computed(
    () =>
        props.importCosts.iedmt_rate_pct === 0 &&
        !props.importCosts.iedmt_exempt,
);

// Without a new value or CO2 the IEDMT is unknown (not € 0): the total then
// only covers the fixed costs and the IEDMT follows in the quote.
const isIedmtUnknown = computed(
    () =>
        !props.importCosts.iedmt_exempt &&
        (props.importCosts.iedmt_eur === null ||
            !props.importCosts.iedmt_determinable),
);

const hasOptionalCosts = computed(() =>
    props.importCosts.fixed_costs.some((cost) => cost.optional),
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
    <article class="flex flex-col rounded-xl border border-border p-5">
        <h3 class="font-display text-lg font-semibold text-foreground">
            Kosten in Spanje
        </h3>
        <p
            class="mt-2 font-display text-3xl font-semibold text-foreground tabular-nums"
        >
            <template v-if="importCosts.total_eur !== null">
                {{ formatEuro(importCosts.total_eur) }}
            </template>
            <template v-else>
                {{ formatEuro(importCosts.fixed_costs_total_eur) }}
                <span
                    class="block font-sans text-sm font-normal text-muted-foreground"
                >
                    + IEDMT, berekenen wij handmatig
                </span>
            </template>
        </p>

        <div
            v-if="isBelowThreshold"
            class="mt-4 flex items-start gap-2 rounded-lg bg-success/10 p-3 text-sm text-foreground"
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
                    Registratiebelasting (IEDMT<template
                        v-if="importCosts.iedmt_rate_pct !== null"
                        >,
                        {{
                            importCosts.iedmt_rate_pct.toLocaleString('nl-NL')
                        }}%</template
                    >)
                </dt>
                <dd class="text-right font-medium tabular-nums">
                    <span
                        v-if="importCosts.iedmt_exempt"
                        class="inline-flex items-center gap-1 text-success"
                    >
                        <BadgeCheck class="size-4" />
                        Vrijgesteld
                    </span>
                    <span
                        v-else-if="
                            isIedmtUnknown || importCosts.iedmt_eur === null
                        "
                        class="text-muted-foreground"
                    >
                        Niet automatisch te bepalen – wij berekenen dit
                        handmatig
                    </span>
                    <span v-else>{{ formatEuro(importCosts.iedmt_eur) }}</span>
                </dd>
            </div>
            <template
                v-if="
                    !importCosts.iedmt_exempt &&
                    importCosts.iedmt_rate_pct !== null &&
                    importCosts.iedmt_rate_pct > 0 &&
                    importCosts.new_value_eur > 0
                "
            >
                <div
                    class="flex items-baseline justify-between gap-3 text-muted-foreground"
                >
                    <dt>
                        {{
                            importCosts.value_source === 'hacienda'
                                ? 'Nieuwwaarde volgens Hacienda'
                                : 'Catalogusprijs nieuw'
                        }}
                        <span
                            v-if="importCosts.hacienda_model"
                            class="block text-xs"
                        >
                            {{ importCosts.hacienda_model }}
                        </span>
                    </dt>
                    <dd class="tabular-nums">
                        {{ formatEuro(importCosts.new_value_eur) }}
                    </dd>
                </div>
                <div
                    class="flex items-baseline justify-between gap-3 text-muted-foreground"
                >
                    <dt>
                        Marktwaarde ({{ importCosts.retention_pct }}% na
                        afschrijving)
                    </dt>
                    <dd class="tabular-nums">
                        {{ formatEuro(importCosts.estimated_market_value_eur) }}
                    </dd>
                </div>
            </template>

            <div class="my-2 border-t border-border"></div>

            <div
                v-for="cost in importCosts.fixed_costs"
                :key="cost.key"
                class="flex items-baseline justify-between gap-3"
            >
                <dt class="text-muted-foreground">
                    {{ cost.label }}
                    <span v-if="cost.optional" class="block text-xs">
                        Niet inbegrepen in het totaal
                    </span>
                </dt>
                <dd
                    class="text-right font-medium tabular-nums"
                    :class="{ 'text-muted-foreground': cost.optional }"
                >
                    {{ formatEuro(cost.amount_eur) }}
                </dd>
            </div>

            <div
                class="mt-2 flex items-baseline justify-between gap-3 border-t border-border pt-3"
            >
                <dt class="font-semibold text-foreground">
                    Totaal kosten Spanje
                    <span
                        v-if="importCosts.total_eur === null"
                        class="block text-xs font-normal text-muted-foreground"
                    >
                        Exclusief IEDMT, die berekenen wij handmatig
                    </span>
                    <span
                        v-else-if="hasOptionalCosts"
                        class="block text-xs font-normal text-muted-foreground"
                    >
                        Exclusief optionele posten
                    </span>
                </dt>
                <dd
                    class="text-right font-display text-lg font-semibold text-foreground tabular-nums"
                >
                    {{
                        formatEuro(
                            importCosts.total_eur ??
                                importCosts.fixed_costs_total_eur,
                        )
                    }}
                </dd>
            </div>
        </dl>

        <div
            v-if="importCosts.iedmt_exempt && importCosts.iedmt_exempt_reason"
            class="mt-4 flex items-start gap-2 rounded-lg bg-success/10 p-3 text-sm text-foreground"
        >
            <BadgeCheck class="mt-0.5 size-4 shrink-0 text-success" />
            <p>{{ importCosts.iedmt_exempt_reason }}</p>
        </div>

        <div
            v-for="note in importCosts.notes"
            :key="note"
            class="mt-3 flex items-start gap-2 rounded-lg bg-warning/15 p-3 text-sm text-foreground"
        >
            <AlertTriangle class="mt-0.5 size-4 shrink-0 text-warning" />
            <p>{{ note }}</p>
        </div>

        <div class="mt-3 flex items-start gap-2 text-sm text-muted-foreground">
            <Info class="mt-0.5 size-4 shrink-0" />
            <p>
                Vaste kosten zijn een richtprijs op basis van onze
                partner-tarieven. Het exacte bedrag hangt af van de gekozen
                autonome regio en eventueel aanvullende werkzaamheden
                (specifieke homologatie, vertalingen).
            </p>
        </div>
    </article>
</template>
