<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
import ContactStrip from '@/components/ContactStrip.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface PostSummary {
    slug: string;
    title: string;
    excerpt: string | null;
    hero_image_url: string | null;
    hero_image_srcset: string | null;
    published_at: string | null;
    author: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PostsPaginator {
    data: PostSummary[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
}

type PaginationKind = 'previous' | 'next' | 'page';

interface PaginationItem extends PaginationLink {
    kind: PaginationKind;
    text: string;
}

const props = defineProps<{
    posts: PostsPaginator;
}>();

const offerteHref = '/#offerte';

const isCompactLayout = computed(() => props.posts.data.length <= 2);

/**
 * Laravel's paginator always puts "previous" first and "next" last; their
 * labels arrive as HTML entities ("&laquo; Previous"), so we label them ourselves.
 */
const paginationItems = computed<PaginationItem[]>(() => {
    const lastIndex = props.posts.links.length - 1;

    return props.posts.links.map((link, index) => {
        if (index === 0) {
            return { ...link, kind: 'previous', text: 'Vorige' };
        }

        if (index === lastIndex) {
            return { ...link, kind: 'next', text: 'Volgende' };
        }

        return { ...link, kind: 'page', text: link.label };
    });
});

const dateFormatter = new Intl.DateTimeFormat('nl-NL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

function formatDate(iso: string | null): string {
    if (!iso) {
        return '';
    }

    const d = new Date(iso);

    return Number.isNaN(d.getTime()) ? '' : dateFormatter.format(d);
}
</script>

<template>
    <PublicLayout>
        <section class="bg-secondary/40">
            <div
                class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-20"
            >
                <h1
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-5xl"
                >
                    Praktische gidsen voor uw Spaanse import
                </h1>
                <p
                    class="mx-auto mt-4 max-w-2xl text-base text-muted-foreground md:text-lg"
                >
                    Stappenplannen, fiscale toelichting en ervaring uit de
                    praktijk — geschreven voor Nederlanders die hun auto naar
                    Spanje verhuizen.
                </p>
            </div>
        </section>

        <section class="container mx-auto px-4 py-12 md:py-16">
            <div
                v-if="posts.data.length === 0"
                class="mx-auto max-w-2xl text-center"
            >
                <h2 class="font-display text-2xl font-semibold text-foreground">
                    Nog geen artikelen
                </h2>
                <p class="mt-3 text-muted-foreground">
                    Er staan nog geen artikelen online. De meest gestelde vragen
                    over BPM, ITV en matriculación vindt u al bij de
                    <Link
                        href="/veelgestelde-vragen"
                        class="font-medium text-primary underline underline-offset-4 hover:text-primary/80"
                        >veelgestelde vragen</Link
                    >. Wilt u direct weten wat het u kost? Vul uw kenteken in
                    voor een indicatie.
                </p>
            </div>

            <!-- 1–2 posts: single readable column -->
            <div
                v-else-if="isCompactLayout"
                class="mx-auto flex max-w-3xl flex-col gap-6"
            >
                <article
                    v-for="post in posts.data"
                    :key="post.slug"
                    class="group overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-shadow hover:shadow-md"
                >
                    <Link
                        :href="`/blog/${post.slug}`"
                        class="flex h-full flex-col focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none sm:flex-row"
                    >
                        <div
                            v-if="post.hero_image_url"
                            class="aspect-[16/10] w-full shrink-0 overflow-hidden bg-muted sm:aspect-auto sm:w-2/5"
                        >
                            <!-- At most two cards, both at the top: likely the LCP, so not lazy. -->
                            <img
                                :src="post.hero_image_url"
                                :srcset="post.hero_image_srcset ?? undefined"
                                sizes="(min-width: 640px) 40vw, 100vw"
                                alt=""
                                class="size-full object-cover"
                                fetchpriority="high"
                            />
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <h2
                                class="font-display text-xl font-semibold text-foreground transition-colors group-hover:text-primary md:text-2xl"
                            >
                                {{ post.title }}
                            </h2>
                            <p
                                v-if="post.excerpt"
                                class="mt-2 line-clamp-3 text-muted-foreground"
                            >
                                {{ post.excerpt }}
                            </p>
                            <div
                                class="mt-auto flex items-center gap-2 pt-5 text-sm text-muted-foreground"
                            >
                                <time
                                    v-if="post.published_at"
                                    :datetime="post.published_at"
                                    >{{ formatDate(post.published_at) }}</time
                                >
                                <span
                                    v-if="post.author && post.published_at"
                                    aria-hidden="true"
                                    >·</span
                                >
                                <span v-if="post.author">{{
                                    post.author
                                }}</span>
                            </div>
                        </div>
                    </Link>
                </article>
            </div>

            <!-- 3+ posts: grid -->
            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="(post, index) in posts.data"
                    :key="post.slug"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-shadow hover:shadow-md"
                >
                    <Link
                        :href="`/blog/${post.slug}`"
                        class="flex h-full flex-col focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        <div
                            v-if="post.hero_image_url"
                            class="aspect-[16/10] w-full overflow-hidden bg-muted"
                        >
                            <img
                                :src="post.hero_image_url"
                                :srcset="post.hero_image_srcset ?? undefined"
                                sizes="(min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw"
                                alt=""
                                class="size-full object-cover"
                                :loading="index < 3 ? 'eager' : 'lazy'"
                            />
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <h2
                                class="font-display text-xl font-semibold text-foreground transition-colors group-hover:text-primary"
                            >
                                {{ post.title }}
                            </h2>
                            <p
                                v-if="post.excerpt"
                                class="mt-2 line-clamp-3 text-sm text-muted-foreground"
                            >
                                {{ post.excerpt }}
                            </p>
                            <div
                                class="mt-auto flex items-center gap-2 pt-5 text-sm text-muted-foreground"
                            >
                                <time
                                    v-if="post.published_at"
                                    :datetime="post.published_at"
                                    >{{ formatDate(post.published_at) }}</time
                                >
                                <span
                                    v-if="post.author && post.published_at"
                                    aria-hidden="true"
                                    >·</span
                                >
                                <span v-if="post.author">{{
                                    post.author
                                }}</span>
                            </div>
                        </div>
                    </Link>
                </article>
            </div>

            <!-- Pagination -->
            <nav
                v-if="posts.links.length > 3"
                class="mt-10 flex flex-wrap justify-center gap-2"
                aria-label="Pagina-navigatie"
            >
                <component
                    :is="item.url ? Link : 'span'"
                    v-for="(item, idx) in paginationItems"
                    :key="idx"
                    :href="item.url ?? undefined"
                    :aria-current="item.active ? 'page' : undefined"
                    :aria-disabled="item.url ? undefined : 'true'"
                    class="inline-flex h-11 min-w-11 items-center justify-center gap-1 rounded-xl border border-border px-3 text-sm font-medium transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    :class="{
                        'border-primary bg-primary text-primary-foreground':
                            item.active,
                        'bg-card text-foreground hover:bg-muted':
                            !item.active && item.url,
                        'cursor-not-allowed bg-card text-muted-foreground':
                            !item.url,
                    }"
                >
                    <ChevronLeft
                        v-if="item.kind === 'previous'"
                        class="size-4"
                        aria-hidden="true"
                    />
                    <span>{{ item.text }}</span>
                    <ChevronRight
                        v-if="item.kind === 'next'"
                        class="size-4"
                        aria-hidden="true"
                    />
                </component>
            </nav>
        </section>

        <!-- Closing -->
        <section class="border-t border-border bg-secondary/30">
            <div class="container mx-auto max-w-5xl px-4 py-16 md:py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="font-display text-3xl font-semibold text-balance text-foreground md:text-4xl"
                    >
                        Weten wat het voor uw auto betekent?
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Vul uw kenteken in en zie direct wat u aan BPM
                        terugkrijgt en wat de inschrijving in Spanje kost.
                    </p>
                    <Link
                        :href="offerteHref"
                        class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-accent px-6 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        Bereken wat het u kost
                    </Link>
                </div>

                <ContactStrip class="mt-12" />
            </div>
        </section>
    </PublicLayout>
</template>
