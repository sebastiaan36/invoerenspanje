<script setup lang="ts">
import { Star } from 'lucide-vue-next';
import { computed } from 'vue';

export interface Review {
    name: string;
    location: string;
    rating: number;
    date: string;
    text: string;
    source: string;
    is_example: boolean;
}

const props = defineProps<{
    reviews: Review[];
}>();

const hasExamples = computed(() => props.reviews.some((r) => r.is_example));

const monthFormatter = new Intl.DateTimeFormat('nl-NL', {
    month: 'long',
    year: 'numeric',
});

function formatMonth(yearMonth: string): string {
    const [year, month] = yearMonth.split('-').map(Number);

    return monthFormatter.format(new Date(year, (month ?? 1) - 1, 1));
}
</script>

<template>
    <section
        v-if="reviews.length"
        class="border-t border-border bg-background"
        aria-labelledby="reviews-title"
    >
        <div class="container mx-auto max-w-5xl px-4 py-16 md:py-20">
            <h2
                id="reviews-title"
                class="font-display text-3xl font-semibold text-balance text-foreground md:text-4xl"
            >
                Wat klanten zeggen
            </h2>
            <p
                v-if="hasExamples"
                class="mt-3 inline-flex rounded-lg border border-warning/40 bg-warning/10 px-3 py-1.5 text-sm text-foreground"
            >
                Voorbeeldreviews om het ontwerp te bekijken. Alleen lokaal
                zichtbaar.
            </p>

            <ul class="mt-10 grid gap-6 md:grid-cols-3">
                <li
                    v-for="review in reviews"
                    :key="review.name + review.date"
                    class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm"
                >
                    <figure class="flex h-full flex-col">
                        <div class="flex items-center justify-between gap-3">
                            <p
                                class="flex gap-0.5"
                                role="img"
                                :aria-label="`${review.rating} van 5 sterren`"
                            >
                                <Star
                                    v-for="n in 5"
                                    :key="n"
                                    class="size-4"
                                    :class="
                                        n <= review.rating
                                            ? 'fill-accent text-accent'
                                            : 'text-border'
                                    "
                                    aria-hidden="true"
                                />
                            </p>
                            <span
                                v-if="review.is_example"
                                class="rounded-md bg-warning/15 px-2 py-0.5 text-xs font-semibold text-foreground"
                            >
                                Voorbeeld
                            </span>
                        </div>
                        <blockquote
                            class="mt-4 flex-1 text-base leading-relaxed text-foreground"
                        >
                            <p>“{{ review.text }}”</p>
                        </blockquote>
                        <figcaption class="mt-5 text-sm text-muted-foreground">
                            <span class="font-medium text-foreground">{{
                                review.name
                            }}</span>
                            · {{ review.location }}
                            <span class="block">
                                {{ formatMonth(review.date) }} · via
                                {{ review.source }}
                            </span>
                        </figcaption>
                    </figure>
                </li>
            </ul>
        </div>
    </section>
</template>
