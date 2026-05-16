<script setup lang="ts">
import { computed } from 'vue';
import { Sparkles } from 'lucide-vue-next';

const props = defineProps<{
    iedmtSavingsEur: number;
}>();

const euroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

const formattedSavings = computed(() => euroFormatter.format(props.iedmtSavingsEur));
</script>

<template>
    <section
        class="rounded-2xl border-2 border-accent bg-accent/10 p-6 shadow-md sm:p-8"
        role="status"
    >
        <header class="flex items-start gap-4">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-accent text-accent-foreground">
                <Sparkles class="size-5" />
            </div>
            <div class="flex-1">
                <div class="text-xs font-semibold uppercase tracking-wider text-accent">
                    Vrijstelling van toepassing
                </div>
                <h3 class="mt-1 font-display text-2xl font-semibold text-foreground">
                    Goed nieuws — u komt in aanmerking voor IEDMT-vrijstelling
                </h3>
            </div>
        </header>

        <p class="mt-4 text-base leading-relaxed text-foreground">
            Omdat u uw vaste verblijfplaats verplaatst naar Spanje en de auto al
            meer dan 6 maanden op uw naam staat, bent u vrijgesteld van de
            Spaanse registratiebelasting. Dat scheelt u in dit geval circa
            <strong class="font-display text-2xl text-accent">{{ formattedSavings }}</strong>.
        </p>

        <p class="mt-4 text-sm text-muted-foreground">
            De vrijstelling moet binnen 60 dagen na inschrijving in de gemeente
            (empadronamiento) worden aangevraagd. Wij regelen dit voor u in het
            <strong class="font-medium text-foreground">Compleet-pakket</strong>.
        </p>
    </section>
</template>
