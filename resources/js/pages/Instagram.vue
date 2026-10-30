<script setup lang="ts">
import {
    ArrowRight,
    BookOpen,
    Calculator,
    MessageCircle,
    Receipt,
} from 'lucide-vue-next';
import { WHATSAPP_URL } from '@/lib/contact';

interface InstagramPost {
    slug: string;
    title: string;
    hero_image_url: string | null;
    hero_image_srcset: string | null;
}

withDefaults(defineProps<{ posts?: InstagramPost[] }>(), {
    posts: () => [],
});

// Instagram visitors land here from the bio link. The UTM tags travel along
// to the price check, so a quote request is stored with source "social".
const priceCheckUrl =
    '/?utm_source=instagram&utm_medium=social&utm_campaign=bio#kentekencheck';

const secondaryLinks = [
    {
        label: 'Bereken uw BPM-teruggave',
        href: '/bpm-calculator',
        icon: Receipt,
    },
    {
        label: 'Pakketten en prijzen',
        href: '/tarieven',
        icon: BookOpen,
    },
    {
        label: 'Stel uw vraag via WhatsApp',
        href: WHATSAPP_URL,
        icon: MessageCircle,
        external: true,
    },
];
</script>

<template>
    <main class="min-h-svh bg-secondary/30">
        <div class="mx-auto flex max-w-md flex-col px-4 pt-10 pb-12">
            <header class="text-center">
                <div class="flex justify-center -space-x-3">
                    <img
                        src="/images/maikel.jpeg"
                        alt="Maikel"
                        width="64"
                        height="64"
                        class="size-16 rounded-full border-2 border-background object-cover shadow-sm"
                    />
                    <img
                        src="/images/sebastiaan.jpeg"
                        alt="Sebastiaan"
                        width="64"
                        height="64"
                        class="size-16 rounded-full border-2 border-background object-cover shadow-sm"
                    />
                </div>
                <p
                    class="mt-4 font-display text-lg font-semibold tracking-tight text-primary"
                >
                    autoinvoeren<span class="text-accent">spanje</span>.nl
                </p>
                <h1
                    class="mt-2 font-display text-2xl leading-tight font-semibold text-balance text-foreground"
                >
                    Uw Nederlandse auto op Spaans kenteken
                </h1>
                <p class="mt-2 text-pretty text-muted-foreground">
                    Wij regelen het hele traject aan de Costa del Sol, van
                    BPM-teruggave tot ITV. In het Nederlands, met vaste prijzen.
                </p>
            </header>

            <nav aria-label="Snelle links" class="mt-8 flex flex-col gap-3">
                <a
                    :href="priceCheckUrl"
                    class="flex min-h-14 items-center justify-center gap-2 rounded-xl bg-accent px-5 text-lg font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                >
                    <Calculator class="size-5" aria-hidden="true" />
                    Bereken direct uw prijs
                </a>
                <p class="-mt-1 text-center text-sm text-muted-foreground">
                    Vul uw kenteken in en zie de netto kosten, gratis en
                    vrijblijvend.
                </p>

                <a
                    v-for="link in secondaryLinks"
                    :key="link.href"
                    :href="link.href"
                    :target="link.external ? '_blank' : undefined"
                    :rel="link.external ? 'noopener' : undefined"
                    class="flex min-h-13 items-center gap-3 rounded-xl border border-border bg-card px-5 font-medium text-foreground shadow-sm transition-colors hover:bg-secondary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                >
                    <component
                        :is="link.icon"
                        class="size-5 shrink-0 text-accent"
                        aria-hidden="true"
                    />
                    <span class="flex-1">{{ link.label }}</span>
                    <ArrowRight
                        class="size-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                </a>
            </nav>

            <section v-if="posts.length" class="mt-10" aria-labelledby="lees-meer">
                <h2
                    id="lees-meer"
                    class="font-display text-lg font-semibold text-foreground"
                >
                    Meer weten? Lees verder
                </h2>
                <ul class="mt-3 flex flex-col gap-3">
                    <li v-for="post in posts" :key="post.slug">
                        <a
                            :href="`/blog/${post.slug}`"
                            class="flex items-center gap-3 overflow-hidden rounded-xl border border-border bg-card p-2 pr-4 shadow-sm transition-colors hover:bg-secondary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        >
                            <img
                                v-if="post.hero_image_url"
                                :src="post.hero_image_url"
                                :srcset="post.hero_image_srcset ?? undefined"
                                sizes="80px"
                                alt=""
                                width="80"
                                height="60"
                                loading="lazy"
                                class="h-15 w-20 shrink-0 rounded-lg object-cover"
                            />
                            <span
                                class="flex-1 text-sm leading-snug font-medium text-foreground"
                            >
                                {{ post.title }}
                            </span>
                        </a>
                    </li>
                </ul>
            </section>

            <a
                href="/auto-invoeren-spanje"
                class="mt-4 inline-flex min-h-11 items-center justify-center gap-1 text-sm font-medium text-primary underline-offset-4 hover:underline"
            >
                Of lees de complete gids
                <ArrowRight class="size-4" aria-hidden="true" />
            </a>

            <footer class="mt-8 text-center text-sm text-muted-foreground">
                <a
                    href="/"
                    class="inline-flex min-h-11 items-center underline-offset-4 hover:underline"
                >
                    Naar de website
                </a>
            </footer>
        </div>
    </main>
</template>
