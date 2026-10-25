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
        foreach (config('seo.routes') as $routeName => $meta) {
            $app = $this->appHtml($this->get(route($routeName))->assertOk()->getContent());

            $this->assertStringContainsString('<h1', $app, "{$routeName} has no server-rendered h1");
            $this->assertStringContainsString(e($meta['h1']), $app, "{$routeName} h1 differs from config");
            $this->assertStringContainsString('href="/tarieven"', $app, "{$routeName} lacks crawlable internal links");
        }
    }

    public function test_homepage_prints_long_form_copy_and_faq_without_javascript(): void
    {
        $app = $this->appHtml($this->get(route('home'))->getContent());

        $this->assertStringContainsString('De stappen van het importproces op een rij', $app);
        $this->assertStringContainsString(e(config('faq.home.0.question')), $app);
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
        $this->assertStringContainsString('€ 181,50', $app);
    }

    public function test_guide_page_renders_cost_tables_from_config(): void
    {
        $app = $this->appHtml($this->get(route('gids'))->assertOk()->getContent());

        $this->assertSame(2, substr_count($app, '<table'));
        $this->assertStringContainsString('14,75%', $app);
        $this->assertStringContainsString('€ 395', $app);
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
