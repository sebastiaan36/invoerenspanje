<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Blog\BlogHtmlSanitizer;
use App\Services\HeroImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class BlogController extends Controller
{
    public function __construct(
        private readonly BlogHtmlSanitizer $sanitizer,
        private readonly HeroImageOptimizer $heroImages,
    ) {}

    public function index(Request $request): Response
    {
        $posts = Post::published()
            ->with('author:id,name')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Post $p) => $this->card($p));

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(string $slug): Response
    {
        $post = Post::published()
            ->with('author:id,name')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Post::published()
            ->with('author:id,name')
            ->where('slug', '!=', $slug)
            ->latest('published_at')
            ->take(3)
            ->get()
            ->map(fn (Post $p) => $this->card($p));

        $heroImageUrl = $post->hero_image_path
            ? asset('storage/'.$post->hero_image_path)
            : null;

        return Inertia::render('Blog/Show', [
            'seo' => [
                'title' => trim($post->title).' — autoinvoerenspanje.nl',
                'description' => trim((string) $post->excerpt) ?: config('seo.default.description'),
                'image' => $heroImageUrl ?? (config('seo.default_image') ? url((string) config('seo.default_image')) : null),
            ],
            'post' => [
                'slug' => $post->slug,
                'title' => trim($post->title),
                'excerpt' => $post->excerpt,
                'content_html' => $this->renderContent($post->content_html),
                'hero_image_url' => $heroImageUrl,
                'hero_image_srcset' => $post->hero_image_path ? $this->heroImages->srcset($post->hero_image_path) : null,
                'published_at' => $post->published_at?->toIso8601String(),
                'updated_at' => $post->updated_at?->toIso8601String(),
                'author' => $post->author?->name,
            ],
            'related' => $related,
        ]);
    }

    /**
     * @return array{slug: string, title: string, excerpt: ?string, hero_image_url: ?string, hero_image_srcset: ?string, published_at: ?string, author: ?string}
     */
    private function card(Post $post): array
    {
        return [
            'slug' => $post->slug,
            'title' => trim($post->title),
            'excerpt' => $post->excerpt,
            'hero_image_url' => $post->hero_image_path ? asset('storage/'.$post->hero_image_path) : null,
            'hero_image_srcset' => $post->hero_image_path ? $this->heroImages->srcset($post->hero_image_path) : null,
            'published_at' => $post->published_at?->toIso8601String(),
            'author' => $post->author?->name,
        ];
    }

    /**
     * Authors sometimes paste Markdown into the HTML field. Content without
     * HTML block elements is rendered as Markdown (with any inline HTML
     * escaped) so it never shows raw "##". HTML content goes through the
     * allowlist sanitizer, since it is rendered with v-html.
     */
    private function renderContent(?string $content): string
    {
        $content ??= '';

        if (! preg_match('/<(p|h[1-6]|ul|ol|li|div|br|table|blockquote|figure|img|a|section|article)\b/i', $content)) {
            return Str::markdown($content, [
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ]);
        }

        return $this->sanitizer->sanitize($content);
    }
}
