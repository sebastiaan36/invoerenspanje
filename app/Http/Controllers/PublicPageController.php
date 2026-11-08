<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Review;
use App\Services\Blog\UnpublishedBlogLinkFilter;
use App\Services\HeroImageOptimizer;
use App\Services\Packages\ServicePackage;
use App\Services\Packages\ServicePackages;
use App\Services\SpainImport\SpainFixedCosts;
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
            'contentHtml' => app(UnpublishedBlogLinkFilter::class)->filter(view('content.home', [
                'lowestPackagePrice' => min(array_map(fn (ServicePackage $p): int => $p->priceEur, ServicePackages::all())),
                'fixedCostsTotal' => array_sum(array_column(SpainFixedCosts::fromConfig(), 'amount_eur')),
            ])->render()),
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
            'fixedCosts' => SpainFixedCosts::fromConfig(),
            'faqItems' => config('faq.tarieven'),
            'iedmtSummary' => self::iedmtSummary(config('spain_import.autonomias.costa_del_sol.iedmt_brackets', [])),
        ]);
    }

    /**
     * The IEDMT tiers in one sentence, e.g. "0% tot 120 g/km CO₂, daarboven
     * 4,75%, 9,75% of 14,75% van de waarde van de auto." Built from
     * config/spain_import.php so the page never shows outdated rates.
     *
     * @param  list<array{co2_max_g_per_km: int|null, rate_pct: float|int}>  $brackets
     */
    public static function iedmtSummary(array $brackets): string
    {
        if ($brackets === []) {
            return '';
        }

        $percent = fn (float|int $rate): string => rtrim(rtrim(number_format((float) $rate, 2, ',', ''), '0'), ',').'%';
        $first = array_shift($brackets);
        $summary = "{$percent($first['rate_pct'])} tot {$first['co2_max_g_per_km']} g/km CO₂";

        if ($brackets === []) {
            return $summary.'.';
        }

        $higherRates = array_map(fn (array $bracket): string => $percent($bracket['rate_pct']), $brackets);
        $lastRate = array_pop($higherRates);
        $rates = $higherRates === [] ? $lastRate : implode(', ', $higherRates).' of '.$lastRate;

        return "{$summary}, daarboven {$rates} van de waarde van de auto.";
    }

    public function diensten(): Response
    {
        return Inertia::render('Diensten', [
            'contentHtml' => app(UnpublishedBlogLinkFilter::class)->filter(view('content.diensten')->render()),
        ]);
    }

    public function overOns(): Response
    {
        return Inertia::render('OverOns', [
            'contentHtml' => app(UnpublishedBlogLinkFilter::class)->filter(view('content.over-ons')->render()),
        ]);
    }

    /**
     * The crawler HTML for this page (contact details, opening hours and
     * service area) is resources/views/content/contact.blade.php, included by
     * seo/fallback.blade.php; Contact.vue reads the same config.
     */
    public function contact(): Response
    {
        return Inertia::render('Contact', [
            'werkgebied' => config('werkgebied'),
        ]);
    }

    public function bpmCalculator(): Response
    {
        return Inertia::render('BpmCalculator', [
            'contentHtml' => app(UnpublishedBlogLinkFilter::class)->filter(view('content.bpm-calculator')->render()),
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
            'contentHtml' => app(UnpublishedBlogLinkFilter::class)->filter(view('content.gids')->render()),
            'faqItems' => config('faq.home'),
            'updatedLabel' => 'Bijgewerkt: '.config('seo.routes.gids.updated_label'),
        ]);
    }

    /**
     * Link-in-bio page for Instagram: a price-check button and a hand-picked
     * set of articles, in the order of INSTAGRAM_POSTS. Unpublished slugs are
     * skipped, so the list never shows a dead link.
     */
    public function instagram(HeroImageOptimizer $heroImages): Response
    {
        $newestPost = Post::published()->latest('published_at')->first();

        $handPickedPosts = Post::published()
            ->whereIn('slug', self::INSTAGRAM_POSTS)
            ->when($newestPost, fn ($query) => $query->whereKeyNot($newestPost->getKey()))
            ->get()
            ->sortBy(fn (Post $post): int|false => array_search($post->slug, self::INSTAGRAM_POSTS, true));

        $posts = collect([$newestPost])
            ->filter()
            ->concat($handPickedPosts)
            ->map(fn (Post $post): array => [
                'slug' => $post->slug,
                'title' => trim($post->title),
                'is_newest' => $post->is($newestPost),
                'hero_image_url' => $post->hero_image_path ? asset('storage/'.$post->hero_image_path) : null,
                'hero_image_srcset' => $post->hero_image_path ? $heroImages->srcset($post->hero_image_path) : null,
            ])
            ->values()
            ->all();

        return Inertia::render('Instagram', [
            'posts' => $posts,
        ]);
    }

    /**
     * Articles on the Instagram page, most useful first. The newest
     * published post is always shown above these.
     *
     * @var list<string>
     */
    private const INSTAGRAM_POSTS = [
        'kosten-auto-invoeren-spanje-rekenvoorbeelden',
        'bpm-teruggave-auto-importeren-spanje',
        'wanneer-auto-op-spaans-kenteken-verplicht',
        'auto-naar-spanje-zelf-rijden-of-transport',
        'registratiebelasting-iedmt-spanje-vrijstelling-verhuizing',
        'leaseauto-of-gefinancierde-auto-meenemen-naar-spanje',
    ];
}
