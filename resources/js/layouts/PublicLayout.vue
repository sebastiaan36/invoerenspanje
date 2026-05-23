<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { home, login } from '@/routes';

const currentPath = computed(() => new URL(usePage().url, 'http://x').pathname);

defineProps<{
    title?: string;
    description?: string;
}>();

const navItems = [
    { label: 'Diensten', href: '/diensten' },
    { label: 'Tarieven', href: '/tarieven' },
    { label: 'BPM-calculator', href: '/bpm-calculator' },
    { label: 'Blog', href: '/blog' },
    { label: 'Over ons', href: '/over-ons' },
    { label: 'Contact', href: '/contact' },
];

const dienstenLinks = [
    { label: 'Auto op Spaans kenteken', href: '/diensten/auto-op-spaans-kenteken' },
    { label: 'BPM-teruggave', href: '/diensten/bpm-teruggave' },
    { label: 'Auto-export Nederland', href: '/diensten/auto-export-nederland' },
    { label: 'ITV-begeleiding', href: '/diensten/itv-begeleiding' },
];

const bedrijfLinks = [
    { label: 'Over ons', href: '/over-ons' },
    { label: 'Reviews', href: '/reviews' },
    { label: 'Blog', href: '/blog' },
    { label: 'Veelgestelde vragen', href: '/veelgestelde-vragen' },
];

const year = new Date().getFullYear();
const mobileMenuOpen = ref(false);

function closeMenu() {
    mobileMenuOpen.value = false;
}
</script>

<template>
    <Head v-if="title" :title="title">
        <meta v-if="description" name="description" :content="description" />
    </Head>

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="sticky top-0 z-40 border-b border-border bg-background/90 backdrop-blur">
            <div class="container mx-auto flex h-16 items-center justify-between px-4">
                <Link
                    :href="home().url"
                    class="font-display text-xl font-semibold text-primary tracking-tight"
                >
                    autoinvoeren<span class="text-accent">spanje</span>.nl
                </Link>

                <nav class="hidden items-center gap-7 text-sm font-medium text-foreground/80 md:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="relative transition-colors hover:text-primary"
                        :class="currentPath === item.href ? 'text-primary' : ''"
                    >
                        {{ item.label }}
                        <span
                            v-if="currentPath === item.href"
                            class="absolute -bottom-[21px] left-0 h-0.5 w-full bg-accent"
                        />
                    </Link>
                </nav>

                <div class="flex items-center gap-3">
                    <Link
                        :href="login().url"
                        class="hidden text-sm font-medium text-foreground transition-colors hover:text-primary sm:inline"
                    >
                        Inloggen
                    </Link>
                    <Link
                        href="/"
                        class="hidden rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 sm:inline-flex"
                    >
                        Offerte
                    </Link>
                    <!-- Hamburger -->
                    <button
                        type="button"
                        class="flex items-center justify-center rounded-lg p-2 text-foreground transition-colors hover:bg-secondary md:hidden"
                        :aria-expanded="mobileMenuOpen"
                        aria-label="Menu openen"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <X v-if="mobileMenuOpen" class="size-5" />
                        <Menu v-else class="size-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div
                v-if="mobileMenuOpen"
                class="border-t border-border bg-background/95 backdrop-blur md:hidden"
            >
                <nav class="container mx-auto flex flex-col px-4 py-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center border-b border-border/50 py-3.5 text-sm font-medium text-foreground/80 transition-colors hover:text-primary last:border-0"
                        :class="currentPath === item.href ? 'text-primary' : ''"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </Link>
                    <div class="mt-4 flex gap-3">
                        <Link
                            :href="login().url"
                            class="flex-1 rounded-xl border border-border py-2.5 text-center text-sm font-medium text-foreground transition-colors hover:bg-secondary"
                            @click="closeMenu"
                        >
                            Inloggen
                        </Link>
                        <Link
                            href="/"
                            class="flex-1 rounded-xl bg-accent py-2.5 text-center text-sm font-semibold text-accent-foreground transition-colors hover:bg-accent/90"
                            @click="closeMenu"
                        >
                            Offerte aanvragen
                        </Link>
                    </div>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="bg-primary text-primary-foreground">
            <div class="container mx-auto grid gap-10 px-4 py-14 md:grid-cols-4">
                <div>
                    <div class="font-display text-lg font-semibold">
                        autoinvoeren<span class="text-accent">spanje</span>.nl
                    </div>
                    <p class="mt-3 text-sm text-primary-foreground/70">
                        Voor Nederlanders aan de Costa del Sol die hun auto op Spaans
                        kenteken willen zetten — alles geregeld, niets aan de hand.
                    </p>
                </div>

                <div>
                    <h4 class="mb-3 font-display text-sm font-semibold">Diensten</h4>
                    <ul class="space-y-2 text-sm text-primary-foreground/70">
                        <li v-for="item in dienstenLinks" :key="item.href">
                            <Link :href="item.href" class="transition-colors hover:text-accent">
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-3 font-display text-sm font-semibold">Bedrijf</h4>
                    <ul class="space-y-2 text-sm text-primary-foreground/70">
                        <li v-for="item in bedrijfLinks" :key="item.href">
                            <Link :href="item.href" class="transition-colors hover:text-accent">
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-3 font-display text-sm font-semibold">Contact</h4>
                    <ul class="space-y-2 text-sm text-primary-foreground/70">
                        <li>info@autoinvoerenspanje.nl</li>
                        <li>Málaga, Spanje</li>
                        <li>
                            <Link href="/" class="transition-colors hover:text-accent">
                                Offerte aanvragen
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-primary-foreground/10">
                <div
                    class="container mx-auto flex flex-col gap-2 px-4 py-4 text-xs text-primary-foreground/60 md:flex-row md:items-center md:justify-between"
                >
                    <div>© {{ year }} autoinvoerenspanje.nl — alle rechten voorbehouden</div>
                    <div class="flex gap-4">
                        <Link href="/privacy" class="transition-colors hover:text-accent">
                            Privacy
                        </Link>
                        <Link href="/algemene-voorwaarden" class="transition-colors hover:text-accent">
                            Algemene voorwaarden
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
