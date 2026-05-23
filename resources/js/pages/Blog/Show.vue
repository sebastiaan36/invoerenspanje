<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface Post {
    slug: string;
    title: string;
    excerpt: string | null;
    content_html: string;
    hero_image_url: string | null;
    published_at: string | null;
    author: string | null;
}

interface RelatedPost {
    slug: string;
    title: string;
    excerpt: string | null;
    hero_image_url: string | null;
    published_at: string | null;
    author: string | null;
}

defineProps<{
    post: Post;
    related: RelatedPost[];
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
    <PublicLayout :title="`${post.title} — autoinvoerenspanje.nl`" :description="post.excerpt ?? undefined">
        <article class="bg-background">
            <!-- Breadcrumb / back link -->
            <div class="border-b border-border bg-card/50">
                <div class="container mx-auto max-w-3xl px-4 py-4">
                    <Link
                        href="/blog"
                        class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-primary"
                    >
                        <ArrowLeft class="size-4" />
                        Alle artikelen
                    </Link>
                </div>
            </div>

            <!-- Hero -->
            <header class="container mx-auto max-w-3xl px-4 pt-12 md:pt-16">
                <div class="text-xs font-semibold uppercase tracking-wider text-accent">
                    Blog
                </div>
                <h1 class="mt-3 font-display text-4xl font-semibold leading-tight text-foreground md:text-5xl">
                    {{ post.title }}
                </h1>
                <p
                    v-if="post.excerpt"
                    class="mt-4 text-lg leading-relaxed text-muted-foreground"
                >
                    {{ post.excerpt }}
                </p>
                <div class="mt-6 flex items-center gap-2 text-sm text-muted-foreground">
                    <time v-if="post.published_at" :datetime="post.published_at">
                        {{ formatDate(post.published_at) }}
                    </time>
                    <span v-if="post.author && post.published_at" aria-hidden="true">·</span>
                    <span v-if="post.author">door {{ post.author }}</span>
                </div>
            </header>

            <figure
                v-if="post.hero_image_url"
                class="container mx-auto mt-10 max-w-4xl px-4"
            >
                <img
                    :src="post.hero_image_url"
                    :alt="post.title"
                    class="w-full rounded-2xl shadow-md"
                />
            </figure>

            <!-- Body -->
            <div class="container mx-auto max-w-3xl px-4 py-12 md:py-16">
                <div
                    class="prose max-w-none prose-headings:font-display prose-headings:text-foreground prose-h2:mt-12 prose-h3:mt-8 prose-p:text-foreground prose-strong:text-foreground prose-a:text-accent prose-a:font-medium hover:prose-a:underline prose-code:text-primary prose-blockquote:border-accent prose-blockquote:text-foreground md:prose-lg"
                    v-html="post.content_html"
                />
            </div>

            <!-- Meer artikelen -->
            <div v-if="related.length > 0" class="border-t border-border bg-secondary/30">
                <div class="container mx-auto max-w-5xl px-4 py-14">
                    <h2 class="font-display text-2xl font-semibold text-foreground">
                        Meer artikelen
                    </h2>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="item in related"
                            :key="item.slug"
                            :href="`/blog/${item.slug}`"
                            class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div class="aspect-[16/9] w-full overflow-hidden bg-muted">
                                <img
                                    v-if="item.hero_image_url"
                                    :src="item.hero_image_url"
                                    :alt="item.title"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-secondary"
                                >
                                    <span class="text-3xl font-bold text-accent">B</span>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(item.published_at) }}
                                </p>
                                <h3 class="mt-2 font-display text-base font-semibold leading-snug text-foreground group-hover:text-accent transition-colors">
                                    {{ item.title }}
                                </h3>
                                <p v-if="item.excerpt" class="mt-2 line-clamp-2 text-sm text-muted-foreground">
                                    {{ item.excerpt }}
                                </p>
                                <span class="mt-4 text-xs font-semibold text-accent">
                                    Lees verder →
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- CTA back -->
            <div class="border-t border-border bg-secondary/30">
                <div class="container mx-auto max-w-3xl px-4 py-10 text-center">
                    <p class="text-sm text-muted-foreground">
                        Vragen over uw eigen situatie?
                    </p>
                    <Link
                        href="/"
                        class="mt-3 inline-flex items-center justify-center rounded-xl bg-accent px-5 py-3 text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90"
                    >
                        Bereken direct uw indicatie
                    </Link>
                </div>
            </div>
        </article>
    </PublicLayout>
</template>
