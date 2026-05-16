<script setup lang="ts">
import { computed } from 'vue';
import { Check, Clock, XCircle } from 'lucide-vue-next';

const PHASES = [
    { key: 'offerte', label: 'Offerte' },
    { key: 'akkoord', label: 'Akkoord' },
    { key: 'in_uitvoering', label: 'In uitvoering' },
    { key: 'afgerond', label: 'Afgerond' },
] as const;

type PhaseKey = (typeof PHASES)[number]['key'];
type Status = PhaseKey | 'concept' | 'geannuleerd';

const props = defineProps<{
    status: Status;
}>();

const currentIndex = computed(() => {
    const idx = PHASES.findIndex((p) => p.key === props.status);
    if (idx >= 0) return idx;
    if (props.status === 'concept') return -1;
    return PHASES.length; // unknown → all done style
});

function phaseState(idx: number): 'completed' | 'current' | 'upcoming' {
    if (idx < currentIndex.value) return 'completed';
    if (idx === currentIndex.value) return 'current';
    return 'upcoming';
}
</script>

<template>
    <!-- Geannuleerd: aparte pill -->
    <div
        v-if="status === 'geannuleerd'"
        class="flex items-center gap-3 rounded-2xl border border-destructive/30 bg-destructive/5 p-4"
    >
        <XCircle class="size-5 shrink-0 text-destructive" />
        <div>
            <div class="font-display text-sm font-semibold text-destructive">Dossier geannuleerd</div>
            <p class="text-xs text-muted-foreground">
                Neem contact op met onze uitvoerder voor meer informatie.
            </p>
        </div>
    </div>

    <!-- Timeline -->
    <ol v-else class="relative grid grid-cols-4 gap-2">
        <!-- Connecting line -->
        <div class="pointer-events-none absolute left-4 right-4 top-4 h-px bg-border" aria-hidden="true" />

        <li
            v-for="(phase, idx) in PHASES"
            :key="phase.key"
            class="relative flex flex-col items-center text-center"
        >
            <div
                class="z-10 flex size-8 items-center justify-center rounded-full border-2 transition-colors"
                :class="{
                    'border-success bg-success text-white': phaseState(idx) === 'completed',
                    'border-accent bg-accent text-accent-foreground ring-4 ring-accent/20': phaseState(idx) === 'current',
                    'border-border bg-card text-muted-foreground': phaseState(idx) === 'upcoming',
                }"
            >
                <Check v-if="phaseState(idx) === 'completed'" class="size-4 stroke-[3]" />
                <Clock v-else-if="phaseState(idx) === 'current'" class="size-4" />
                <span v-else class="text-xs font-semibold">{{ idx + 1 }}</span>
            </div>
            <div
                class="mt-2 text-xs font-medium leading-tight"
                :class="{
                    'text-foreground': phaseState(idx) !== 'upcoming',
                    'text-muted-foreground': phaseState(idx) === 'upcoming',
                }"
            >
                {{ phase.label }}
            </div>
            <div
                v-if="phaseState(idx) === 'current'"
                class="mt-1 text-xs font-semibold uppercase tracking-wider text-accent"
            >
                Nu
            </div>
        </li>
    </ol>
</template>
