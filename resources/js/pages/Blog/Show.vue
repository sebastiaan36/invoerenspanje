<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import ContactStrip from '@/components/ContactStrip.vue';
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

const props = defineProps<{
    post: Post;
    related: RelatedPost[];
}>();

const offerteHref = '/#offerte';

/** Team members with a real photo; other authors are shown without an avatar. */
const authorPhotos: Record<string, string> = {
    Sebastiaan: '/images/sebastiaan.jpeg',
    Maikel: '/images/maikel.jpeg',
};

const authorPhoto = computed<string | null>(() => {
    const firstName = props.post.author?.trim().split(/\s+/)[0];

    return firstName ? (authorPhotos[firstName] ?? null) : null;
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
        <article class="bg-background">
            <!-- Breadcrumb / back link -->
            <div class="border-b border-border bg-card/50">
                <div class="container mx-auto max-w-3xl px-4 py-1">
                    <Link
                        href="/blog"
                        class="-mx-2 inline-flex min-h-11 items-center gap-1.5 rounded-lg px-2 text-sm text-muted-foreground transition-colors hover:text-primary focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        <ArrowLeft class="size-4" />
                        Alle artikelen
                    </Link>
                </div>
            </div>

            <!-- Hero -->
            <header class="container mx-auto max-w-3xl px-4 pt-12 md:pt-16">
                <h1
                    class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-5xl"
                >
                    {{ post.title }}
                </h1>
                <p
                    v-if="post.excerpt"
                    class="mt-4 max-w-2xl text-lg leading-relaxed text-muted-foreground"
                >
                    {{ post.excerpt }}
                </p>
                <div
                    v-if="post.author || post.published_at"
                    class="mt-6 flex items-center gap-3 text-sm text-muted-foreground"
                >
                    <img
                        v-if="authorPhoto"
                        :src="authorPhoto"
                        alt=""
                        width="40"
                        height="40"
                        class="size-10 shrink-0 rounded-full border border-border object-cover"
                    />
                    <p class="flex flex-wrap items-center gap-x-2">
                        <span v-if="post.author"
                            >door
                            <span class="font-medium text-foreground">{{
                                post.author
                            }}</span></span
                        >
                        <span
                            v-if="post.author && post.published_at"
                            aria-hidden="true"
                            >·</span
                        >
                        <time
                            v-if="post.published_at"
                            :datetime="post.published_at"
                            >{{ formatDate(post.published_at) }}</time
                        >
                    </p>
                </div>
            </header>

            <figure
                v-if="post.hero_image_url"
                class="container mx-auto mt-10 max-w-4xl px-4"
            >
                <!-- Fixed 16:9 box reserves the space before the image loads (no CLS);
                     the hero is the LCP element, so fetch it first. -->
                <img
                    :src="post.hero_image_url"
                    alt=""
                    width="1600"
                    height="900"
                    fetchpriority="high"
                    decoding="async"
                    class="aspect-[16/9] w-full rounded-2xl object-cover shadow-sm"
                />
            </figure>

            <!-- Body -->
            <div class="container mx-auto max-w-3xl px-4 py-12 md:py-16">
                <div
                    class="prose prose-lg max-w-[68ch] prose-headings:font-display prose-headings:font-semibold prose-headings:text-foreground prose-h2:mt-12 prose-h3:mt-8 prose-p:text-foreground prose-a:font-medium prose-a:text-primary prose-a:underline prose-a:underline-offset-4 hover:prose-a:text-primary/80 prose-blockquote:border-l-2 prose-blockquote:border-border prose-blockquote:font-normal prose-blockquote:text-foreground prose-strong:text-foreground prose-code:text-primary prose-li:text-foreground prose-li:marker:text-muted-foreground"
                    v-html="post.content_html"
                />
            </div>

            <!-- Meer artikelen -->
            <div
                v-if="related.length > 0"
                class="border-t border-border bg-secondary/30"
            >
                <div class="container mx-auto max-w-5xl px-4 py-14">
                    <h2
                        class="font-display text-2xl font-semibold text-foreground"
                    >
                        Meer artikelen
                    </h2>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="item in related"
                            :key="item.slug"
                            :href="`/blog/${item.slug}`"
                            class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-shadow hover:shadow-md focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        >
                            <div
                                v-if="item.hero_image_url"
                                class="aspect-[16/9] w-full overflow-hidden bg-muted"
                            >
                                <img
                                    :src="item.hero_image_url"
                                    alt=""
                                    class="size-full object-cover"
                                    loading="lazy"
                                />
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <time
                                    v-if="item.published_at"
                                    :datetime="item.published_at"
                                    class="text-sm text-muted-foreground"
                                    >{{ formatDate(item.published_at) }}</time
                                >
                                <h3
                                    class="mt-2 font-display text-lg leading-snug font-semibold text-foreground transition-colors group-hover:text-primary"
                                >
                                    {{ item.title }}
                                </h3>
                                <p
                                    v-if="item.excerpt"
                                    class="mt-2 line-clamp-2 text-sm text-muted-foreground"
                                >
                                    {{ item.excerpt }}
                                </p>
                                <span
                                    class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold text-accent"
                                >
                                    Lees verder
                                    <ArrowRight
                                        class="size-4"
                                        aria-hidden="true"
                                    />
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Closing CTA -->
            <div class="border-t border-border bg-background">
                <div class="container mx-auto max-w-5xl px-4 py-16 md:py-20">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2
                            class="font-display text-3xl font-semibold text-balance text-foreground"
                        >
                            Vragen over uw eigen situatie?
                        </h2>
                        <p class="mt-3 text-lg text-muted-foreground">
                            Vul uw kenteken in en zie wat u aan BPM terugkrijgt
                            en wat de inschrijving in Spanje kost.
                        </p>
                        <Link
                            :href="offerteHref"
                            class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-accent px-6 py-3 text-center text-sm font-semibold text-accent-foreground shadow-sm transition-colors hover:bg-accent/90 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        >
                            Bereken wat het u kost en vraag een offerte aan
                        </Link>
                    </div>

                    <ContactStrip class="mt-12" />
                </div>
            </div>
        </article>
    </PublicLayout>
</template>
