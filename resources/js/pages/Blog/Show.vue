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

defineProps<{
    post: Post;
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
                    class="aspect-[16/9] w-full rounded-2xl object-cover shadow-md"
                />
            </figure>

            <!-- Body -->
            <div class="container mx-auto max-w-3xl px-4 py-12 md:py-16">
                <div
                    class="prose prose-lg max-w-none prose-headings:font-display prose-headings:text-foreground prose-h2:mt-12 prose-h3:mt-8 prose-p:text-foreground prose-strong:text-foreground prose-a:text-accent prose-a:font-medium hover:prose-a:underline prose-code:text-primary prose-blockquote:border-accent prose-blockquote:text-foreground"
                    v-html="post.content_html"
                />
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
