<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_vue_pages_get_their_hero_copy_from_the_shared_seo_prop(): void
    {
        foreach (array_filter(config('seo.routes'), fn (array $meta): bool => isset($meta['h1'])) as $routeName => $meta) {
            $this->get(route($routeName))->assertInertia(fn (Assert $page) => $page
                ->where('seo.h1', $meta['h1'])
                ->where('seo.h1_accent', $meta['h1_accent'] ?? null)
                ->where('seo.intro', $meta['intro'] ?? null)
                ->etc());
        }
    }

    /**
     * Guards against drift: the hero copy lives in config/seo.php only, not
     * hardcoded a second time in the Vue pages.
     */
    public function test_vue_pages_do_not_hardcode_the_hero_copy(): void
    {
        $vueSource = collect(glob(resource_path('js/pages/{*,Blog/*}.vue'), GLOB_BRACE))
            ->map(fn (string $path): string => preg_replace('/\s+/', ' ', file_get_contents($path)))
            ->implode("\n");

        foreach (config('seo.routes') as $routeName => $meta) {
            foreach (['h1', 'intro', 'intro_short'] as $key) {
                // Short h1s like "Over ons" also occur as plain words; only longer copy is checked.
                if (isset($meta[$key]) && mb_strlen($meta[$key]) > 25) {
                    $this->assertStringNotContainsString($meta[$key], $vueSource, "{$routeName}.{$key} is hardcoded in a Vue page");
                }
            }
        }
    }

    public function test_diensten_and_over_ons_print_their_full_body_copy(): void
    {
        $diensten = $this->get(route('diensten'));
        $dienstenApp = $this->appHtml($diensten->getContent());

        foreach (['Wat wij voor u regelen', 'Auto-export Nederland', 'BPM-teruggave', 'Auto op Spaans kenteken', 'ITV-begeleiding', 'Exportmelding via een erkende RDW-exportpartner', 'Daarnaast mogelijk', 'Aankoopbegeleiding'] as $copy) {
            $this->assertStringContainsString($copy, $dienstenApp);
        }
        // Same source for Vue: the page renders the content view through v-html.
        $diensten->assertInertia(fn (Assert $page) => $page
            ->component('Diensten')
            ->where('contentHtml', fn (string $html): bool => str_contains($html, 'Exportmelding via een erkende RDW-exportpartner')));

        $overOns = $this->get(route('over-ons'));
        $overOnsApp = $this->appHtml($overOns->getContent());

        foreach (['Onze missie', 'Het team', 'Maikel Mobility', 'HBO Automotive aan de IVA', 'Been &amp; van Dam', 'Waar wij voor staan', 'Nederlandstalig, met vaste prijzen'] as $copy) {
            $this->assertStringContainsString($copy, $overOnsApp);
        }
        $this->assertStringContainsString('id="maikel"', $overOnsApp, 'The schema Person @id anchors must exist');
        $overOns->assertInertia(fn (Assert $page) => $page
            ->component('OverOns')
            ->where('contentHtml', fn (string $html): bool => str_contains($html, 'Maikel Mobility')));
    }

    public function test_contact_page_prints_contact_details_hours_and_service_area(): void
    {
        $app = $this->appHtml($this->get(route('contact'))->assertOk()->getContent());

        $this->assertStringContainsString('href="'.config('contact.whatsapp_url').'"', $app);
        $this->assertStringContainsString('href="mailto:'.config('contact.email').'"', $app);
        foreach (config('contact.opening_hours') as $openingHours) {
            $this->assertStringContainsString(e($openingHours['days']).': '.e($openingHours['hours']), $app);
        }
        $this->assertStringContainsString(e(config('werkgebied.intro')), $app);
        $this->assertStringContainsString(e(config('werkgebied.plaatsen.0.naam')), $app);
    }

    public function test_contact_details_come_from_one_config_source(): void
    {
        config([
            'contact.phone' => '+34600111222',
            'contact.phone_display' => '+34 600 111 222',
            'contact.email' => 'test@example.com',
        ]);

        $response = $this->get(route('contact'));
        $html = $response->getContent();

        $business = collect($this->jsonLdGraph($html))->firstWhere('@type', 'ProfessionalService');
        $this->assertSame('+34600111222', $business['telephone']);
        $this->assertSame('test@example.com', $business['contactPoint']['email']);
        $this->assertStringContainsString('<a href="tel:+34600111222">+34 600 111 222</a>', $this->appHtml($html));
        $response->assertInertia(fn (Assert $page) => $page
            ->where('contact.phone', '+34600111222')
            ->where('contact.email', 'test@example.com')
            ->etc());

        // The front end reads the shared prop instead of keeping its own copy.
        $contactModule = file_get_contents(resource_path('js/lib/contact.ts'));
        $this->assertStringNotContainsString('650015942', $contactModule);
        $this->assertStringNotContainsString('@autoinvoerenspanje.nl', $contactModule);
    }

    public function test_iedmt_tiers_are_generated_from_the_config(): void
    {
        config(['spain_import.autonomias.costa_del_sol.iedmt_brackets' => [
            ['co2_max_g_per_km' => 110, 'rate_pct' => 0.0],
            ['co2_max_g_per_km' => 150, 'rate_pct' => 5.5],
            ['co2_max_g_per_km' => null, 'rate_pct' => 16],
        ]]);
        $expected = '0% tot 110 g/km CO₂, daarboven 5,5% of 16% van de waarde van de auto.';

        $response = $this->get(route('tarieven'));

        $this->assertStringContainsString('Registratiebelasting (IEDMT): '.$expected, $this->appHtml($response->getContent()));
        $response->assertInertia(fn (Assert $page) => $page->where('iedmtSummary', $expected));

        $gids = $this->appHtml($this->get(route('gids'))->getContent());
        $this->assertStringContainsString('0% tot 16%, afhankelijk van CO₂', $gids);
        $this->assertStringContainsString("Auto's tot\n        110 g/km CO₂ betalen geen IEDMT", $gids);

        foreach (['js/pages/Tarieven.vue', 'views/seo/fallback.blade.php'] as $file) {
            $this->assertStringNotContainsString('14,75%', file_get_contents(resource_path($file)), "{$file} hardcodes an IEDMT rate");
        }
    }

    public function test_iedmt_summary_lists_the_current_tiers(): void
    {
        $this->get(route('tarieven'))->assertInertia(fn (Assert $page) => $page
            ->where('iedmtSummary', '0% tot 120 g/km CO₂, daarboven 4,75%, 9,75% of 14,75% van de waarde van de auto.'));
    }

    public function test_paginated_blog_pages_keep_their_page_in_the_canonical_and_link_prev_next(): void
    {
        config(['app.url' => 'https://autoinvoerenspanje.nl']);
        $author = User::factory()->create();
        foreach (range(1, 10) as $number) {
            Post::create(['title' => "Artikel {$number}", 'slug' => "artikel-{$number}", 'content_html' => '<p>x</p>', 'status' => Post::STATUS_PUBLISHED, 'published_at' => now()->subDays($number), 'author_id' => $author->id]);
        }

        $first = $this->get(route('blog.index'))->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="https://autoinvoerenspanje.nl/blog">', $first);
        $this->assertStringContainsString('<a href="/blog?page=2" rel="next">', $this->appHtml($first));
        $this->assertStringNotContainsString('rel="prev"', $this->appHtml($first));

        $second = $this->get(route('blog.index', ['page' => 2]))->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="https://autoinvoerenspanje.nl/blog?page=2">', $second);
        $this->assertStringContainsString('<a href="/blog" rel="prev">', $this->appHtml($second));
        $this->assertStringNotContainsString('rel="next"', $this->appHtml($second));

        $this->get(route('blog.index', ['page' => 1]))
            ->assertSee('<link rel="canonical" href="https://autoinvoerenspanje.nl/blog">', false);
        $this->get(route('blog.index', ['page' => 'abc']))
            ->assertSee('<link rel="canonical" href="https://autoinvoerenspanje.nl/blog">', false);
    }

    public function test_homepage_prints_long_form_copy_and_faq_without_javascript(): void
    {
        $app = $this->appHtml($this->get(route('home'))->getContent());

        $this->assertStringContainsString('Auto importeren in Spanje: de stappen op een rij', $app);
        $this->assertStringContainsString(e(config('faq.home.0.question')), $app);
    }

    public function test_homepage_hero_is_server_rendered_with_the_vue_markup(): void
    {
        $app = $this->appHtml($this->get(route('home'))->getContent());

        // Same classes as Welcome.vue, so the LCP paragraph paints from HTML.
        $this->assertStringContainsString('<p class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground">'.e(config('seo.routes.home.intro')).'</p>', $app);
        $this->assertStringContainsString('<h1 class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-6xl">Uw auto <span class="text-accent">invoeren in Spanje</span>, zonder gedoe.</h1>', $app);
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

    public function test_bpm_calculator_mentions_the_minimum_refund(): void
    {
        $app = $this->appHtml($this->get(route('bpm-calculator'))->assertOk()->getContent());
        $minimum = config('bpm_rates.minimum_refund_eur');

        $this->assertStringContainsString("De teruggave komt uit op minimaal €&nbsp;{$minimum}.", $app);
        $this->assertStringContainsString("Komt de rest-BPM uit onder €&nbsp;{$minimum}", $app);
    }

    public function test_homepage_links_to_the_guide_and_supporting_articles(): void
    {
        $slugs = [
            'wanneer-auto-op-spaans-kenteken-verplicht',
            'auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het',
            'itv-keuring-auto-import-spanje',
            'registratiebelasting-iedmt-spanje-vrijstelling-verhuizing',
        ];
        // Links to posts that are not published are rendered as plain text.
        $author = User::factory()->create();
        foreach ($slugs as $slug) {
            Post::create(['title' => $slug, 'slug' => $slug, 'content_html' => '<p>x</p>', 'status' => Post::STATUS_PUBLISHED, 'published_at' => now()->subDay(), 'author_id' => $author->id]);
        }

        $app = $this->appHtml($this->get(route('home'))->getContent());

        $this->assertStringContainsString('<a href="/auto-invoeren-spanje">auto invoeren in Spanje</a>', $app);

        foreach ($slugs as $slug) {
            $this->assertStringContainsString('href="/blog/'.$slug.'"', $app);
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
