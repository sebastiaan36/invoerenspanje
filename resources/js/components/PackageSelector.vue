<script setup lang="ts">
import { Check, Star } from 'lucide-vue-next';
import { trackEvent } from '@/lib/analytics';

export interface ServicePackage {
    slug: string;
    name: string;
    price_eur: number;
    tagline: string;
    recommended: boolean;
    features: string[];
}

const props = defineProps<{
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
    if (selected.value !== slug) {
        const pkg = props.packages.find((p) => p.slug === slug);

        trackEvent('select_package', {
            package_slug: slug,
            package_price: pkg?.price_eur,
            location: 'kentekencheck',
        });
    }

    selected.value = slug;
}
</script>

<template>
    <section>
        <header class="text-center">
            <h3
                class="font-display text-2xl font-semibold text-foreground sm:text-3xl"
            >
                Welk pakket past bij u?
            </h3>
            <p class="mx-auto mt-2 max-w-xl text-base text-muted-foreground">
                Van zelf doen met begeleiding tot alles uit handen, inclusief
                transport. Het bedrag hierboven past zich aan uw keuze aan.
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
                class="group relative flex h-full flex-col overflow-hidden rounded-2xl border-2 p-6 text-left transition-[border-color,background-color,box-shadow] focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                :class="
                    selected === pkg.slug
                        ? 'border-accent bg-accent/5 shadow-md'
                        : 'border-border bg-card shadow-sm hover:border-input hover:shadow-md'
                "
            >
                <!-- Selected indicator: same terracotta language as the choice tiles -->
                <div
                    class="absolute top-5 right-5 flex size-6 items-center justify-center rounded-full border-2 transition-colors"
                    :class="
                        selected === pkg.slug
                            ? 'border-accent bg-accent text-accent-foreground'
                            : 'border-input'
                    "
                    aria-hidden="true"
                >
                    <Check
                        v-if="selected === pkg.slug"
                        class="size-3.5 stroke-[3]"
                    />
                </div>

                <div class="pr-8">
                    <h4
                        class="font-display text-2xl font-semibold text-foreground"
                    >
                        {{ pkg.name }}
                    </h4>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ pkg.tagline }}
                    </p>
                    <p
                        v-if="pkg.recommended"
                        class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-foreground"
                    >
                        <Star
                            class="size-4 fill-current text-warning"
                            aria-hidden="true"
                        />
                        Aanbevolen
                    </p>
                </div>

                <div
                    class="mt-5 font-display text-4xl font-semibold text-foreground tabular-nums"
                >
                    {{ formatEuro(pkg.price_eur) }}
                </div>
                <div class="text-sm text-muted-foreground">
                    eenmalig, vaste prijs
                </div>

                <ul class="mt-6 space-y-2.5 text-sm">
                    <li
                        v-for="feature in pkg.features"
                        :key="feature"
                        class="flex items-start gap-2"
                    >
                        <Check
                            class="mt-0.5 size-4 shrink-0 stroke-[3] text-success"
                        />
                        <span class="text-foreground">{{ feature }}</span>
                    </li>
                </ul>

                <div class="mt-auto pt-6">
                    <span
                        class="flex min-h-11 w-full items-center justify-center gap-1.5 rounded-xl border text-sm font-semibold transition-colors"
                        :class="
                            selected === pkg.slug
                                ? 'border-accent bg-accent text-accent-foreground'
                                : 'border-input text-foreground group-hover:bg-secondary'
                        "
                    >
                        <Check
                            v-if="selected === pkg.slug"
                            class="size-4 stroke-[3]"
                            aria-hidden="true"
                        />
                        {{
                            selected === pkg.slug
                                ? 'Gekozen'
                                : 'Kies dit pakket'
                        }}
                    </span>
                </div>
            </button>
        </div>
    </section>
</template>
