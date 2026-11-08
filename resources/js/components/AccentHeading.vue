<script setup lang="ts">
import { computed } from 'vue';

/**
 * A heading whose `accent` part is shown in the accent colour, e.g. the page
 * h1 from config/seo.php (`h1` + `h1_accent`). The crawler HTML in
 * resources/views/seo/fallback.blade.php splits the text the same way.
 */
const props = withDefaults(
    defineProps<{
        text: string;
        accent?: string | null;
        as?: string;
    }>(),
    {
        accent: null,
        as: 'h1',
    },
);

const parts = computed(() => {
    const index = props.accent ? props.text.indexOf(props.accent) : -1;

    if (!props.accent || index === -1) {
        return { before: props.text, accent: '', after: '' };
    }

    return {
        before: props.text.slice(0, index),
        accent: props.accent,
        after: props.text.slice(index + props.accent.length),
    };
});
</script>

<template>
    <component :is="as"
        >{{ parts.before
        }}<span v-if="parts.accent" class="text-accent">{{ parts.accent }}</span
        >{{ parts.after }}</component
    >
</template>
