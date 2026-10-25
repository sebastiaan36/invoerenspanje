<script setup lang="ts">
import { BadgeCheck } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    iedmtSavingsEur: number;
}>();

const euroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

const formattedSavings = computed(() =>
    euroFormatter.format(props.iedmtSavingsEur),
);
</script>

<template>
    <section
        class="rounded-2xl border border-success/30 bg-success/5 p-6 sm:p-8"
        role="status"
    >
        <header class="flex items-start gap-3">
            <BadgeCheck class="mt-1 size-6 shrink-0 text-success" />
            <h3 class="font-display text-xl font-semibold text-foreground">
                U komt waarschijnlijk in aanmerking voor vrijstelling van de
                Spaanse registratiebelasting
            </h3>
        </header>

        <p class="mt-3 text-base leading-relaxed text-foreground">
            Omdat u uw vaste verblijfplaats verplaatst naar Spanje en de auto al
            meer dan 6 maanden op uw naam staat, bent u vrijgesteld van de
            Spaanse registratiebelasting. Dat scheelt u in dit geval circa
            <strong class="font-semibold text-success">{{
                formattedSavings
            }}</strong
            >. Dat bedrag is al verwerkt in de berekening hierboven.
        </p>

        <p class="mt-4 text-sm text-muted-foreground">
            De vrijstelling moet binnen 60 dagen na inschrijving in de gemeente
            (empadronamiento) worden aangevraagd. Wij regelen dit voor u in het
            <strong class="font-medium text-foreground">Compleet-pakket</strong
            >.
        </p>
    </section>
</template>
