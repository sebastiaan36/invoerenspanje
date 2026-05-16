<script setup lang="ts">
import { Check, Star } from 'lucide-vue-next';

export interface ServicePackage {
    slug: string;
    name: string;
    price_eur: number;
    tagline: string;
    recommended: boolean;
    features: string[];
}

defineProps<{
    packages: ServicePackage[];
}>();

const selected = defineModel<string | null>('selected', { default: null });

const euroFormatter = new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

function formatEuro(value: number): string {
    return euroFormatter.format(value);
}

function selectPackage(slug: string) {
    selected.value = slug;
}
</script>

<template>
    <section>
        <header class="text-center">
            <div class="text-xs font-semibold uppercase tracking-wider text-accent">
                Pakketkeuze
            </div>
            <h3 class="mt-1 font-display text-2xl font-semibold text-foreground sm:text-3xl">
                Welk pakket past bij u?
            </h3>
            <p class="mx-auto mt-2 max-w-xl text-sm text-muted-foreground">
                Drie transparante pakketten — van zelf-doen met begeleiding tot
                volledig zorgenpakket inclusief transport. U beslist hoeveel u
                uit handen geeft.
            </p>
        </header>

        <div
            class="mt-8 grid gap-4 md:grid-cols-3 md:items-stretch"
            role="radiogroup"
            aria-label="Service-pakketten"
        >
            <button
                v-for="pkg in packages"
                :key="pkg.slug"
                type="button"
                role="radio"
                :aria-checked="selected === pkg.slug"
                @click="selectPackage(pkg.slug)"
                class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-card p-6 text-left transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/50"
                :class="{
                    'border-2 border-primary shadow-lg': selected === pkg.slug,
                    'border-2 border-accent shadow-md hover:shadow-lg': pkg.recommended && selected !== pkg.slug,
                    'border border-border shadow-sm hover:border-muted-foreground hover:shadow-md': !pkg.recommended && selected !== pkg.slug,
                }"
            >
                <!-- Aanbevolen badge -->
                <div
                    v-if="pkg.recommended"
                    class="absolute -top-px left-6 inline-flex items-center gap-1 rounded-b-md bg-accent px-2.5 py-1 text-xs font-semibold uppercase tracking-wider text-accent-foreground"
                >
                    <Star class="size-3 fill-current" />
                    Aanbevolen
                </div>

                <!-- Selected indicator -->
                <div
                    v-if="selected === pkg.slug"
                    class="absolute right-4 top-4 flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground"
                    aria-hidden="true"
                >
                    <Check class="size-4 stroke-[3]" />
                </div>

                <div :class="pkg.recommended ? 'mt-4' : ''">
                    <h4 class="font-display text-2xl font-semibold text-primary">
                        {{ pkg.name }}
                    </h4>
                    <p class="mt-1 text-sm text-muted-foreground">{{ pkg.tagline }}</p>
                </div>

                <div class="mt-5 font-display text-4xl font-semibold tabular-nums text-accent">
                    {{ formatEuro(pkg.price_eur) }}
                </div>
                <div class="-mt-1 text-xs text-muted-foreground">eenmalig</div>

                <ul class="mt-6 space-y-2.5 text-sm">
                    <li
                        v-for="feature in pkg.features"
                        :key="feature"
                        class="flex items-start gap-2"
                    >
                        <Check class="mt-0.5 size-4 shrink-0 stroke-[3] text-success" />
                        <span class="text-foreground">{{ feature }}</span>
                    </li>
                </ul>

                <div class="mt-auto pt-6 text-xs font-medium uppercase tracking-wider text-muted-foreground transition-colors group-hover:text-foreground">
                    <span v-if="selected === pkg.slug" class="text-primary">
                        ✓ Geselecteerd
                    </span>
                    <span v-else>Klik om te kiezen</span>
                </div>
            </button>
        </div>
    </section>
</template>
