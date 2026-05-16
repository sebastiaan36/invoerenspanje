<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-display leading-tight font-semibold">
            autoinvoeren<span class="text-accent">spanje</span>.nl
        </span>
        <span class="truncate text-xs text-muted-foreground">Klantportaal</span>
    </div>
</template>
