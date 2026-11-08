<?php

namespace App\Http\Middleware;

use App\Services\Packages\ServicePackages;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // Static config: sent on the first visit only, kept by the client after that.
            'packages' => Inertia::once(fn () => ServicePackages::toSharedArray()),
            'contact' => Inertia::once(fn (): array => config('contact')),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'portal' => fn () => $request->user()?->isKlant()
                ? ['unreadMessages' => $request->user()->unreadAdminMessagesCount()]
                : null,
            'seo' => fn () => $this->resolveSeo($request),
        ];
    }

    /**
     * Resolve the SEO meta data for the current route.
     *
     * Controllers may override this by passing their own `seo` prop (e.g. blog
     * posts), which takes precedence over the route-name lookup below.
     *
     * `h1`, `h1_accent`, `intro` and `intro_short` are the page hero copy, so
     * the Vue page and the crawler HTML (seo/fallback.blade.php) print the same text.
     *
     * @return array{title: string, description: string, image: ?string, h1: ?string, h1_accent: ?string, intro: ?string, intro_short: ?string}
     */
    protected function resolveSeo(Request $request): array
    {
        $routeName = $request->route()?->getName();

        // Route names contain dots (blog.index), so look the key up directly
        // instead of through dot-notation, which would split it.
        $meta = config('seo.routes', [])[$routeName] ?? [];

        $image = $meta['image'] ?? config('seo.default_image');

        return [
            'title' => $meta['title'] ?? config('seo.default.title'),
            'description' => $meta['description'] ?? config('seo.default.description'),
            'image' => $image ? $this->absoluteImageUrl($image) : null,
            'h1' => $meta['h1'] ?? null,
            'h1_accent' => $meta['h1_accent'] ?? null,
            'intro' => $meta['intro'] ?? null,
            'intro_short' => $meta['intro_short'] ?? null,
        ];
    }

    /**
     * Expand a root-relative image path to an absolute URL for social tags.
     */
    protected function absoluteImageUrl(string $image): string
    {
        return str_starts_with($image, 'http') ? $image : url($image);
    }
}
