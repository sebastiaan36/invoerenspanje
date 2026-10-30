<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The host can't run Node, so public pages print their core content as plain
 * HTML inside #app. These tests read the raw response, the way a crawler that
 * doesn't execute JavaScript would.
 */
class SeoServerRenderingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Extract the server-rendered markup inside #app.
     */
    private function appHtml(string $html): string
    {
        $start = strpos($html, '<div id="app">');
        $this->assertNotFalse($start, 'The page has no #app container.');

        return substr($html, $start, strrpos($html, '</body>') - $start);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function jsonLdGraph(string $html): array
    {
        $this->assertSame(1, preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $match));

        $schema = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $schema['@context']);

        return $schema['@graph'];
    }

    public function test_every_public_page_renders_its_heading_and_intro_server_side(): void
    {
        // Entries without an h1 (the noindex Instagram page) have no crawler HTML.
        foreach (array_filter(config('seo.routes'), fn (array $meta): bool => isset($meta['h1'])) as $routeName => $meta) {
            $app = $this->appHtml($this->get(route($routeName))->assertOk()->getContent());

            $this->assertSame(1, preg_match('#<h1[^>]*>(.*?)</h1>#s', $app, $h1), "{$routeName} has no server-rendered h1");
            // Compare the text: the homepage h1 wraps part of it in a styled span, like Welcome.vue.
            $this->assertSame(e($meta['h1']), trim(strip_tags($h1[1])), "{$routeName} h1 differs from config");
            $this->assertStringContainsString('href="/tarieven"', $app, "{$routeName} lacks crawlable internal links");
        }
    }

    public function test_homepage_prints_long_form_copy_and_faq_without_javascript(): void
    {
        $app = $this->appHtml($this->get(route('home'))->getContent());

        $this->assertStringContainsString('De stappen van het importproces op een rij', $app);
        $this->assertStringContainsString(e(config('faq.home.0.question')), $app);
    }

    public function test_homepage_hero_is_server_rendered_with_the_vue_markup(): void
    {
        $app = $this->appHtml($this->get(route('home'))->getContent());

        // Same classes as Welcome.vue, so the LCP paragraph paints from HTML.
        $this->assertStringContainsString('<p class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground">'.e(config('seo.routes.home.intro')).'</p>', $app);
        $this->assertStringContainsString('<span class="text-accent">Spaans kenteken</span>', $app);
        $this->assertStringContainsString('class="container mx-auto flex h-16 items-center justify-between px-4"', $app);
    }

    public function test_faq_page_prints_every_question(): void
    {
        $app = $this->appHtml($this->get(route('faq'))->getContent());

        foreach (config('faq.categories') as $category) {
            foreach ($category['items'] as $item) {
                $this->assertStringContainsString(e($item['question']), $app);
            }
        }
    }

    public function test_tarieven_prints_packages_and_fixed_costs(): void
    {
        $app = $this->appHtml($this->get(route('tarieven'))->getContent());

        $this->assertStringContainsString('Compleet Plus', $app);
        $this->assertStringContainsString('Honorarios gestoría', $app);
        // Non-breaking space: the amount never wraps away from the € sign.
        $this->assertStringContainsString("€\u{00A0}181,50", $app);
    }

    public function test_guide_page_renders_cost_tables_from_config(): void
    {
        $app = $this->appHtml($this->get(route('gids'))->assertOk()->getContent());

        $this->assertSame(2, substr_count($app, '<table'));
        $this->assertStringContainsString('14,75%', $app);
        $this->assertStringContainsString('€ 395', $app);
    }

    /**
     * The printed depreciation table must match the table the calculator uses.
     */
    public function test_bpm_calculator_prints_a_depreciation_table_matching_the_config(): void
    {
        $app = $this->appHtml($this->get(route('bpm-calculator'))->assertOk()->getContent());

        $this->assertStringContainsString('Bereken uw rest-BPM bij export', $app);
        $this->assertStringContainsString('Afschrijving BPM', $app);

        $rows = [6 => '6 maanden', 12 => '1 jaar', 24 => '2 jaar', 36 => '3 jaar', 48 => '4 jaar', 60 => '5 jaar', 72 => '6 jaar', 84 => '7 jaar', 96 => '8 jaar', 120 => '10 jaar'];

        foreach ($rows as $months => $label) {
            $tier = collect(config('bpm_rates.depreciation_table'))->first(fn (array $tier): bool => $months <= $tier['max_months']);
            // per_month values like 0.583 approximate 7/12, so drop that float noise before rounding to whole percents.
            $depreciated = (int) round(round($tier['base_percentage'] + ($months - $tier['base_months']) * $tier['per_month'], 1));

            $this->assertStringContainsString("<tr><td>{$label}</td><td>ca. {$depreciated}%</td><td>ca. ".(100 - $depreciated).'%</td></tr>', $app);
        }
    }

    public function test_homepage_links_to_the_guide_and_supporting_articles(): void
    {
        $app = $this->appHtml($this->get(route('home'))->getContent());

        $this->assertStringContainsString('<a href="/auto-invoeren-spanje">auto invoeren in Spanje</a>', $app);

        foreach ([
            '/blog/wanneer-auto-op-spaans-kenteken-verplicht',
            '/blog/auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het',
            '/blog/itv-keuring-auto-import-spanje',
            '/blog/registratiebelasting-iedmt-spanje-vrijstelling-verhuizing',
        ] as $href) {
            $this->assertStringContainsString('href="'.$href.'"', $app);
        }
    }

    public function test_blog_post_body_is_in_the_raw_html(): void
    {
        $author = User::factory()->create(['name' => 'Sebastiaan']);
        Post::create([
            'title' => '  BPM-teruggave uitgelegd',
            'slug' => 'bpm-uitgelegd',
            'excerpt' => 'Samenvatting.',
            'content_html' => '<h2>Zo werkt het</h2><p>Uitleg over de rest-BPM.</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $html = $this->get(route('blog.show', 'bpm-uitgelegd'))->getContent();
        $app = $this->appHtml($html);

        $this->assertStringContainsString('Uitleg over de rest-BPM.', $app);
        $this->assertStringContainsString('<title>BPM-teruggave uitgelegd — autoinvoerenspanje.nl</title>', $html);
        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);

        $article = collect($this->jsonLdGraph($html))->firstWhere('@type', 'BlogPosting');
        $this->assertSame('BPM-teruggave uitgelegd', $article['headline']);
        $this->assertStringEndsWith('/over-ons#sebastiaan', $article['author']['@id']);
    }

    public function test_public_pages_emit_a_valid_json_ld_graph(): void
    {
        $types = collect($this->jsonLdGraph($this->get(route('tarieven'))->getContent()))->pluck('@type');

        $this->assertTrue($types->contains('ProfessionalService'));
        $this->assertTrue($types->contains('BreadcrumbList'));
        $this->assertTrue($types->contains('Service'));
        $this->assertFalse($types->contains('AggregateRating'));
    }

    public function test_schema_and_fallback_show_the_current_phone_number(): void
    {
        $html = $this->get(route('contact'))->getContent();

        $business = collect($this->jsonLdGraph($html))->firstWhere('@type', 'ProfessionalService');
        $this->assertSame('+34650015942', $business['telephone']);
        $this->assertStringContainsString('<a href="tel:+34650015942">+34 650 015 942</a>', $html);
        $this->assertStringNotContainsString('618969732', $html);
    }

    public function test_canonical_is_built_from_app_url_not_the_request(): void
    {
        config(['app.url' => 'https://autoinvoerenspanje.nl']);

        $this->get(route('tarieven'))
            ->assertSee('<link rel="canonical" href="https://autoinvoerenspanje.nl/tarieven">', false);
        $this->get(route('home'))
            ->assertSee('<link rel="canonical" href="https://autoinvoerenspanje.nl">', false);
    }

    public function test_non_public_pages_are_noindex_and_get_no_fallback_content(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringNotContainsString('rel="canonical"', $html);
        $this->assertStringNotContainsString('application/ld+json', $html);
        $this->assertStringNotContainsString('Hoofdmenu', $this->appHtml($html));
    }

    public function test_sitemap_lists_the_guide_with_real_lastmod_dates(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('gids'), false)
            ->assertSee('<lastmod>'.Carbon::parse(config('seo.routes.gids.updated'))->toAtomString().'</lastmod>', false)
            ->assertDontSee('<priority>', false);
    }

    public function test_responses_carry_security_headers(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_inline_scripts_carry_the_csp_nonce_from_the_report_only_policy(): void
    {
        $response = $this->get(route('home'));

        $this->assertStringContainsString("object-src 'none'", (string) $response->headers->get('Content-Security-Policy'));
        $reportOnly = (string) $response->headers->get('Content-Security-Policy-Report-Only');
        $this->assertMatchesRegularExpression("/'nonce-([^']+)'/", $reportOnly);
        preg_match("/'nonce-([^']+)'/", $reportOnly, $matches);

        $this->assertStringContainsString('<script nonce="'.$matches[1].'">', (string) $response->getContent());

        // GA4 beacons that PageSpeed reported as violations of the report-only policy.
        $this->assertStringContainsString('https://*.g.doubleclick.net', $reportOnly);
        $this->assertStringContainsString('https://*.google.es', $reportOnly);
    }

    public function test_admin_panel_carries_security_headers(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_robots_txt_references_the_sitemap(): void
    {
        $this->assertStringContainsString(
            'Sitemap: https://autoinvoerenspanje.nl/sitemap.xml',
            file_get_contents(public_path('robots.txt')),
        );
    }

    public function test_blog_link_repair_migration_fixes_known_broken_links(): void
    {
        $migration = require database_path('migrations/2026_10_25_085725_fix_internal_links_in_blog_posts.php');

        $this->assertSame(
            '<a href="/blog/auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het">x</a>',
            $migration::fixLinks('<a href="/blog/exportkenteken-auto-uitschrijven-rdw/">x</a>'),
        );
        $this->assertSame(
            '<a href="/auto-invoeren-spanje">y</a>',
            $migration::fixLinks('<a href="https://autoinvoerenspanje.nl/blog/auto-invoeren-spanje-stappenplan">y</a>'),
        );
        $this->assertSame(
            '<a href="/tarieven">t</a> <a href="/">h</a> <a href="https://other.nl/a/">o</a>',
            $migration::fixLinks('<a href="/tarieven/">t</a> <a href="/">h</a> <a href="https://other.nl/a/">o</a>'),
        );
    }
}
