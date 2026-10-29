<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Mail, Menu, MessageCircle, Phone, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { scrollToQuote } from '@/composables/useScrollToQuote';
import { EMAIL, PHONE, PHONE_DISPLAY, WHATSAPP_URL } from '@/lib/contact';
import { home, login } from '@/routes';

const currentPath = computed(() => new URL(usePage().url, 'http://x').pathname);

// Section pages (blog posts) keep their parent nav item active.
function isActive(href: string): boolean {
    return (
        currentPath.value === href || currentPath.value.startsWith(`${href}/`)
    );
}

// Title/description/Open Graph are rendered server-side from config/seo.php.
// Here we only sync the document <title> on client-side (SPA) navigation,
// reading the shared `seo` prop so there is a single source of truth.
const seoTitle = computed(
    () => (usePage().props.seo as { title?: string } | undefined)?.title,
);

const navItems = [
    { label: 'Diensten', href: '/diensten' },
    { label: 'Tarieven', href: '/tarieven' },
    { label: 'BPM-calculator', href: '/bpm-calculator' },
    { label: 'Blog', href: '/blog' },
    { label: 'Over ons', href: '/over-ons' },
    { label: 'Contact', href: '/contact' },
];

const dienstenLinks = [
    {
        label: 'Auto op Spaans kenteken',
        href: '/diensten#auto-op-spaans-kenteken',
    },
    { label: 'BPM-teruggave', href: '/diensten#bpm-teruggave' },
    { label: 'Auto-export Nederland', href: '/diensten#auto-export-nederland' },
    { label: 'ITV-begeleiding', href: '/diensten#itv-begeleiding' },
];

const gidsLink = {
    label: 'Gids auto invoeren in Spanje',
    href: '/auto-invoeren-spanje',
};

const bedrijfLinks = [
    gidsLink,
    { label: 'Over ons', href: '/over-ons' },
    { label: 'Blog', href: '/blog' },
    { label: 'Veelgestelde vragen', href: '/veelgestelde-vragen' },
];

const year = new Date().getFullYear();
const mobileMenuOpen = ref(false);

function closeMenu() {
    mobileMenuOpen.value = false;
}

// The quote flow lives on the homepage. There, "Offerte" must scroll to it
// instead of re-visiting the page, which would wipe the visitor's calculation.
const isHome = computed(() => currentPath.value === '/');
const offerteHref = '/#offerte';

function scrollToOfferte() {
    closeMenu();
    scrollToQuote();
}

// Mobile has no room for "Offerte aanvragen" in the header. A compact
// "Bereken" button takes its place once the homepage's kenteken check has
// scrolled out of view (and always on other pages), so the primary action
// stays one tap away on a long page. Below 360px it doesn't fit next to the
// logo; the menu still has "Offerte aanvragen" there.
const lookupInView = ref(true);
let lookupObserver: IntersectionObserver | null = null;

onMounted(() => {
    const lookup = document.getElementById('kentekencheck');

    if (!isHome.value || !lookup) {
        return;
    }

    lookupObserver = new IntersectionObserver(([entry]) => {
        lookupInView.value = entry.isIntersecting;
    });
    lookupObserver.observe(lookup);
});

onBeforeUnmount(() => lookupObserver?.disconnect());

const showMobileQuickCta = computed(() => !isHome.value || !lookupInView.value);
</script>

<template>
    <Head v-if="seoTitle" :title="seoTitle" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header
            class="sticky top-0 z-40 border-b border-border bg-background/90 backdrop-blur"
        >
            <div
                class="container mx-auto flex h-16 items-center justify-between px-4"
            >
                <Link
                    :href="home().url"
                    class="-my-2 py-2 font-display text-xl font-semibold tracking-tight text-primary"
                >
                    autoinvoeren<span class="text-accent">spanje</span>.nl
                </Link>

                <nav
                    aria-label="Hoofdmenu"
                    class="hidden items-center gap-7 text-sm font-medium text-foreground/80 lg:flex"
                >
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="relative inline-flex min-h-11 items-center transition-colors hover:text-primary"
                        :class="isActive(item.href) ? 'text-primary' : ''"
                    >
                        {{ item.label }}
                        <span
                            v-if="isActive(item.href)"
                            class="absolute -bottom-[10px] left-0 h-0.5 w-full bg-accent"
                        />
                    </Link>
                </nav>

                <div class="flex items-center gap-1.5 sm:gap-3">
                    <Link
                        :href="login().url"
                        class="hidden min-h-11 items-center text-sm font-medium text-foreground transition-colors hover:text-primary sm:inline-flex"
                    >
                        Inloggen
                    </Link>
                    <component
                        :is="isHome ? 'a' : Link"
                        :href="offerteHref"
                        class="hidden min-h-11 items-center rounded-xl bg-accent px-4 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none sm:inline-flex"
                        @click="
                            isHome &&
                            ($event.preventDefault(), scrollToOfferte())
                        "
                    >
                        Offerte aanvragen
                    </component>
                    <Transition
                        enter-active-class="transition-opacity duration-200"
                        enter-from-class="opacity-0"
                        leave-active-class="transition-opacity duration-150"
                        leave-to-class="opacity-0"
                    >
                        <component
                            :is="isHome ? 'a' : Link"
                            v-if="showMobileQuickCta"
                            :href="offerteHref"
                            class="inline-flex min-h-11 items-center rounded-xl bg-accent px-3 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none max-[359px]:hidden sm:hidden"
                            @click="
                                isHome &&
                                ($event.preventDefault(), scrollToOfferte())
                            "
                        >
                            Bereken
                        </component>
                    </Transition>
                    <!-- Hamburger -->
                    <button
                        type="button"
                        class="flex size-11 items-center justify-center rounded-lg text-foreground transition-colors hover:bg-secondary lg:hidden"
                        :aria-expanded="mobileMenuOpen"
                        :aria-label="
                            mobileMenuOpen ? 'Menu sluiten' : 'Menu openen'
                        "
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
                class="border-t border-border bg-background/95 backdrop-blur lg:hidden"
            >
                <nav class="container mx-auto flex flex-col px-4 py-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center border-b border-border/50 py-3.5 text-sm font-medium text-foreground/80 transition-colors last:border-0 hover:text-primary"
                        :class="isActive(item.href) ? 'text-primary' : ''"
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
                        <component
                            :is="isHome ? 'a' : Link"
                            :href="offerteHref"
                            class="flex-1 rounded-xl bg-accent py-2.5 text-center text-sm font-semibold text-accent-foreground transition-colors hover:bg-accent/90"
                            @click="
                                isHome
                                    ? ($event.preventDefault(),
                                      scrollToOfferte())
                                    : closeMenu()
                            "
                        >
                            Offerte aanvragen
                        </component>
                    </div>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-border bg-brand text-brand-foreground">
            <div
                class="container mx-auto grid gap-10 px-4 py-14 md:grid-cols-4"
            >
                <div>
                    <div class="font-display text-lg font-semibold">
                        autoinvoeren<span class="text-brand-accent">spanje</span
                        >.nl
                    </div>
                    <p class="mt-3 text-sm text-brand-foreground/75">
                        Voor Nederlanders aan de Costa del Sol die hun auto op
                        Spaans kenteken willen zetten — alles geregeld, niets
                        aan de hand.
                    </p>
                </div>

                <div>
                    <h2 class="mb-3 font-display text-sm font-semibold">
                        Diensten
                    </h2>
                    <ul class="space-y-1 text-sm text-brand-foreground/75">
                        <li v-for="item in dienstenLinks" :key="item.href">
                            <Link
                                :href="item.href"
                                class="inline-flex min-h-11 items-center transition-colors hover:text-brand-accent md:min-h-11"
                            >
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="mb-3 font-display text-sm font-semibold">
                        Bedrijf
                    </h2>
                    <ul class="space-y-1 text-sm text-brand-foreground/75">
                        <li v-for="item in bedrijfLinks" :key="item.href">
                            <Link
                                :href="item.href"
                                class="inline-flex min-h-11 items-center transition-colors hover:text-brand-accent md:min-h-11"
                            >
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="mb-3 font-display text-sm font-semibold">
                        Contact
                    </h2>
                    <ul class="space-y-1 text-sm text-brand-foreground/75">
                        <li>
                            <a
                                :href="WHATSAPP_URL"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex min-h-11 items-center gap-2 transition-colors hover:text-brand-accent md:min-h-11"
                            >
                                <MessageCircle class="size-4" />
                                WhatsApp
                            </a>
                        </li>
                        <li>
                            <a
                                :href="`tel:${PHONE}`"
                                class="inline-flex min-h-11 items-center gap-2 transition-colors hover:text-brand-accent md:min-h-11"
                            >
                                <Phone class="size-4" />
                                {{ PHONE_DISPLAY }}
                            </a>
                        </li>
                        <li>
                            <a
                                :href="`mailto:${EMAIL}`"
                                class="inline-flex min-h-11 items-center gap-2 transition-colors hover:text-brand-accent md:min-h-11"
                            >
                                <Mail class="size-4" />
                                {{ EMAIL }}
                            </a>
                        </li>
                        <li class="flex min-h-11 items-center md:min-h-11">
                            Málaga, Spanje
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-brand-foreground/10">
                <div
                    class="container mx-auto flex flex-col gap-2 px-4 py-4 text-sm text-brand-foreground/75 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        © {{ year }} autoinvoerenspanje.nl — alle rechten
                        voorbehouden
                    </div>
                    <div class="flex gap-4">
                        <Link
                            href="/privacy"
                            class="inline-flex min-h-11 items-center transition-colors hover:text-brand-accent md:min-h-11"
                        >
                            Privacy
                        </Link>
                        <Link
                            href="/algemene-voorwaarden"
                            class="inline-flex min-h-11 items-center transition-colors hover:text-brand-accent md:min-h-11"
                        >
                            Algemene voorwaarden
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
