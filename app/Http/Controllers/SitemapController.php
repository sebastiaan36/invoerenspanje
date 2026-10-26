<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

final class SitemapController extends Controller
{
    /**
     * Static pages in sitemap order, keyed by route name. Their <lastmod>
     * comes from the `updated` date in config/seo.php, so it only changes
     * when the content was actually reviewed.
     *
     * @var list<string>
     */
    private const STATIC_ROUTES = [
        'home', 'gids', 'diensten', 'tarieven', 'bpm-calculator',
        'over-ons', 'contact', 'blog.index', 'faq',
    ];

    public function __invoke(): Response
    {
        $posts = Post::published()
            ->orderByDesc('published_at')
            ->get(['slug', 'published_at', 'updated_at']);

        $staticPages = collect(self::STATIC_ROUTES)->map(function (string $routeName) use ($posts): array {
            $updated = config('seo.routes', [])[$routeName]['updated'] ?? null;

            $lastmod = match (true) {
                $routeName === 'blog.index' => $posts->max(fn (Post $post) => $post->updated_at ?? $post->published_at),
                $updated !== null => Carbon::parse($updated),
                default => null,
            };

            return ['url' => route($routeName), 'lastmod' => $lastmod?->toAtomString()];
        });

        return response(
            view('sitemap', compact('staticPages', 'posts'))->render(),
            200,
            ['Content-Type' => 'application/xml', 'Cache-Control' => 'public, max-age=3600'],
        );
    }
}
