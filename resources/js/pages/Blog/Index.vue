<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface PostSummary {
    slug: string;
    title: string;
    excerpt: string | null;
    hero_image_url: string | null;
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

defineProps<{
    posts: PostsPaginator;
}>();

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
    <PublicLayout
        title="Blog — autoinvoerenspanje.nl"
        description="Praktische artikelen over auto-import van Nederland naar Spanje, BPM-teruggave, IEDMT en alles wat erbij komt kijken."
    >
        <section class="bg-secondary/40">
            <div class="container mx-auto max-w-4xl px-4 py-16 text-center md:py-20">
                <span
                    class="inline-flex items-center rounded-full bg-card px-3 py-1 text-xs font-semibold text-secondary-foreground"
                >
                    Blog
                </span>
                <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-foreground md:text-5xl">
                    Praktische gidsen voor uw <span class="text-accent">Spaanse import</span>
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-base text-muted-foreground md:text-lg">
                    Stappenplannen, fiscale toelichting en ervaring uit de praktijk —
                    geschreven voor Nederlanders die hun auto naar Spanje verhuizen.
                </p>
            </div>
        </section>

        <section class="container mx-auto px-4 py-12 md:py-16">
            <div v-if="posts.data.length === 0" class="rounded-2xl border border-border bg-card p-10 text-center">
                <p class="text-muted-foreground">
                    Er zijn nog geen artikelen gepubliceerd. Kom binnenkort terug.
                </p>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="post in posts.data"
                    :key="post.slug"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                >
                    <Link :href="`/blog/${post.slug}`" class="flex h-full flex-col">
                        <div
                            class="aspect-[16/10] w-full overflow-hidden bg-muted"
                        >
                            <img
                                v-if="post.hero_image_url"
                                :src="post.hero_image_url"
                                :alt="post.title"
                                class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex size-full items-center justify-center bg-gradient-to-br from-primary via-primary-light to-accent text-primary-foreground/40"
                            >
                                <span class="font-display text-3xl">autoinvoerenspanje.nl</span>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <h2 class="font-display text-xl font-semibold text-foreground transition-colors group-hover:text-accent">
                                {{ post.title }}
                            </h2>
                            <p
                                v-if="post.excerpt"
                                class="mt-2 line-clamp-3 text-sm text-muted-foreground"
                            >
                                {{ post.excerpt }}
                            </p>
                            <div class="mt-auto flex items-center gap-2 pt-5 text-xs text-muted-foreground">
                                <span>{{ formatDate(post.published_at) }}</span>
                                <span v-if="post.author" aria-hidden="true">·</span>
                                <span v-if="post.author">{{ post.author }}</span>
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
                <!-- eslint-disable vue/no-v-text-v-html-on-component -->
                <component
                    :is="link.url ? Link : 'span'"
                    v-for="(link, idx) in posts.links"
                    :key="idx"
                    :href="link.url ?? undefined"
                    v-html="link.label"
                    class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-md border border-border px-3 text-sm transition-colors"
                    :class="{
                        'bg-primary text-primary-foreground border-primary': link.active,
                        'bg-card text-foreground hover:bg-muted': !link.active && link.url,
                        'bg-card text-muted-foreground/50 cursor-not-allowed': !link.url,
                    }"
                />
                <!-- eslint-enable vue/no-v-text-v-html-on-component -->
            </nav>
        </section>
    </PublicLayout>
</template>
