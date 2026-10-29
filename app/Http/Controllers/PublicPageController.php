<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Review;
use App\Services\HeroImageOptimizer;
use App\Services\Packages\ServicePackage;
use App\Services\Packages\ServicePackages;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Public marketing pages whose copy lives in Blade partials or config, so the
 * same content feeds both the Vue page and the server-rendered crawler HTML.
 */
final class PublicPageController extends Controller
{
    public function home(HeroImageOptimizer $heroImages): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'faqItems' => config('faq.home'),
            'contentHtml' => view('content.home', [
                'lowestPackagePrice' => min(array_map(fn (ServicePackage $p): int => $p->priceEur, ServicePackages::all())),
                'fixedCostsTotal' => array_sum(array_column(config('spain_import.fixed_costs'), 'amount_eur')),
            ])->render(),
            'reviews' => $this->reviews(),
            'recentPosts' => Post::published()
                ->whereNotNull('hero_image_path')
                ->latest('published_at')
                ->take(3)
                ->get()
                ->map(fn (Post $post): array => [
                    'slug' => $post->slug,
                    'title' => trim($post->title),
                    'hero_image_url' => asset('storage/'.$post->hero_image_path),
                    'hero_image_srcset' => $heroImages->srcset($post->hero_image_path),
                ])
                ->all(),
        ]);
    }

    /**
     * Published reviews, managed in the admin (Reviews). The made-up examples
     * from config/reviews.php are only added when explicitly switched on
     * (local design preview), and are flagged so the page labels them as
     * "Voorbeeld".
     *
     * @return list<array{name: string, location: string, rating: int, date: string, text: string, source: string, is_example: bool}>
     */
    private function reviews(): array
    {
        $reviews = Review::published()->get()->map(fn (Review $review): array => $review->toHomepageArray())->all();

        if (config('reviews.show_examples') && ! app()->isProduction()) {
            $examples = array_map(fn (array $example): array => [...$example, 'is_example' => true], config('reviews.examples', []));
            $reviews = [...$reviews, ...$examples];
        }

        return array_values($reviews);
    }

    public function tarieven(): Response
    {
        return Inertia::render('Tarieven', [
            'fixedCosts' => config('spain_import.fixed_costs'),
            'faqItems' => config('faq.tarieven'),
        ]);
    }

    public function bpmCalculator(): Response
    {
        return Inertia::render('BpmCalculator', [
            'contentHtml' => view('content.bpm-calculator')->render(),
        ]);
    }

    public function faq(): Response
    {
        return Inertia::render('Faq', [
            'categories' => config('faq.categories'),
        ]);
    }

    public function gids(): Response
    {
        return Inertia::render('Gids', [
            'contentHtml' => view('content.gids')->render(),
            'faqItems' => config('faq.home'),
            'updatedLabel' => 'Bijgewerkt: '.config('seo.routes.gids.updated_label'),
        ]);
    }
}
