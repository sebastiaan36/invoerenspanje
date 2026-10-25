<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ChevronDown } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

export interface FaqLink {
    label: string;
    href: string;
}

export interface FaqItem {
    id: string;
    question: string;
    answer: string;
    links?: FaqLink[];
}

const props = defineProps<{
    items: FaqItem[];
}>();

const openItems = ref<Set<string>>(new Set());

function toggle(itemId: string): void {
    if (openItems.value.has(itemId)) {
        openItems.value.delete(itemId);
    } else {
        openItems.value.add(itemId);
    }
}

function isOpen(itemId: string): boolean {
    return openItems.value.has(itemId);
}

// A link to /pagina#vraag-id opens that question on arrival.
onMounted(() => {
    const hash = window.location.hash.slice(1);

    if (props.items.some((item) => item.id === hash)) {
        openItems.value.add(hash);
    }
});
</script>

<template>
    <div
        class="divide-y divide-border overflow-hidden rounded-2xl border border-border bg-card"
    >
        <div
            v-for="item in items"
            :id="item.id"
            :key="item.id"
            class="scroll-mt-24"
        >
            <h3 class="font-sans text-base">
                <button
                    :id="`${item.id}-vraag`"
                    type="button"
                    class="flex min-h-11 w-full items-start justify-between gap-3 px-4 py-4 text-left transition-colors hover:bg-secondary/50 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none focus-visible:ring-inset sm:gap-4 sm:px-6 sm:py-5"
                    :aria-expanded="isOpen(item.id)"
                    :aria-controls="`${item.id}-antwoord`"
                    @click="toggle(item.id)"
                >
                    <span class="font-medium text-foreground">{{
                        item.question
                    }}</span>
                    <ChevronDown
                        class="mt-0.5 size-5 shrink-0 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': isOpen(item.id) }"
                        aria-hidden="true"
                    />
                </button>
            </h3>
            <div
                v-show="isOpen(item.id)"
                :id="`${item.id}-antwoord`"
                role="region"
                :aria-labelledby="`${item.id}-vraag`"
                class="border-t border-border bg-background px-4 py-4 sm:px-6 sm:py-5"
            >
                <p
                    class="max-w-prose leading-relaxed text-pretty text-foreground"
                >
                    {{ item.answer }}
                </p>
                <ul
                    v-if="item.links?.length"
                    class="mt-3 flex flex-wrap gap-x-6"
                >
                    <li v-for="link in item.links" :key="link.href">
                        <Link
                            :href="link.href"
                            class="inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-primary underline-offset-4 hover:underline focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        >
                            {{ link.label }}
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
