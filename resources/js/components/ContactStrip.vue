<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, MessageCircle, Phone } from 'lucide-vue-next';
import { PHONE, PHONE_DISPLAY, WHATSAPP_URL } from '@/lib/contact';

withDefaults(
    defineProps<{
        title?: string;
        body?: string;
        secondaryLabel?: string | null;
        secondaryHref?: string | null;
    }>(),
    {
        title: 'Maikel en Sebastiaan',
        body: 'Maikel kent de Spaanse kant van ITV tot DGT en is uw vaste aanspreekpunt. Sebastiaan houdt de berekening en uw dossier online bij.',
        secondaryLabel: 'Over ons',
        secondaryHref: '/over-ons',
    },
);

const team = [
    { name: 'Maikel', photo: '/images/maikel.jpeg' },
    { name: 'Sebastiaan', photo: '/images/sebastiaan.jpeg' },
];
</script>

<template>
    <div
        class="flex flex-col gap-6 rounded-2xl bg-muted/60 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between lg:gap-8"
    >
        <div class="flex items-center gap-5">
            <div class="flex shrink-0 -space-x-3">
                <img
                    v-for="person in team"
                    :key="person.name"
                    :src="person.photo"
                    :alt="person.name"
                    width="64"
                    height="64"
                    loading="lazy"
                    class="size-16 rounded-full border-2 border-card object-cover"
                />
            </div>
            <div>
                <p class="font-display text-xl font-semibold text-foreground">
                    {{ title }}
                </p>
                <p class="mt-1 max-w-md text-muted-foreground">
                    {{ body }}
                </p>
            </div>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <a
                :href="WHATSAPP_URL"
                target="_blank"
                rel="noopener"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-5 text-sm font-semibold whitespace-nowrap text-primary-foreground transition-colors hover:bg-primary/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
            >
                <MessageCircle class="size-4" />
                WhatsApp ons
            </a>
            <a
                :href="`tel:${PHONE}`"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 text-sm font-medium whitespace-nowrap text-foreground transition-colors hover:bg-secondary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
            >
                <Phone class="size-4" />
                {{ PHONE_DISPLAY }}
            </a>
            <Link
                v-if="secondaryLabel && secondaryHref"
                :href="secondaryHref"
                class="inline-flex min-h-11 items-center justify-center gap-1.5 px-2 text-sm font-medium whitespace-nowrap text-primary underline-offset-4 hover:underline"
            >
                {{ secondaryLabel }}
                <ArrowRight class="size-4" />
            </Link>
        </div>
    </div>
</template>
