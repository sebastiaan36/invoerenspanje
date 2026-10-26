<?php

declare(strict_types=1);

namespace Tests\Feature\Blog;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_published_posts_with_past_published_at(): void
    {
        $author = User::factory()->create();

        Post::create([
            'title' => 'Concept',
            'slug' => 'concept',
            'content_html' => '<p>wip</p>',
            'status' => Post::STATUS_DRAFT,
            'author_id' => $author->id,
        ]);

        Post::create([
            'title' => 'Gearchiveerd',
            'slug' => 'gearchiveerd',
            'content_html' => '<p>oud</p>',
            'status' => Post::STATUS_ARCHIVED,
            'published_at' => now()->subYear(),
            'author_id' => $author->id,
        ]);

        Post::create([
            'title' => 'Toekomstig',
            'slug' => 'toekomstig',
            'content_html' => '<p>later</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->addDay(),
            'author_id' => $author->id,
        ]);

        Post::create([
            'title' => 'Live artikel',
            'slug' => 'live-artikel',
            'excerpt' => 'Korte intro',
            'content_html' => '<p>inhoud</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $slugs = collect($response->viewData('page')['props']['posts']['data'])
            ->pluck('slug');
        $this->assertSame(['live-artikel'], $slugs->all());
    }

    public function test_show_passes_html_content_directly(): void
    {
        $author = User::factory()->create(['name' => 'Sebastiaan']);

        Post::create([
            'title' => 'HTML rendering',
            'slug' => 'html-rendering',
            'excerpt' => 'Test',
            'content_html' => '<h2>Kopje</h2><p>Met <strong>vetgedrukte</strong> tekst en een <a href="https://example.com">link</a>.</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $response = $this->get(route('blog.show', 'html-rendering'));

        $response->assertOk();
        $post = $response->viewData('page')['props']['post'];
        $this->assertSame('html-rendering', $post['slug']);
        $this->assertSame('Sebastiaan', $post['author']);
        $this->assertStringContainsString('<h2>Kopje</h2>', $post['content_html']);
        $this->assertStringContainsString('<strong>vetgedrukte</strong>', $post['content_html']);
        $this->assertStringContainsString('<a href="https://example.com">link</a>', $post['content_html']);
        $this->assertArrayHasKey('related', $response->viewData('page')['props']);
    }

    public function test_show_related_excludes_current_post_and_returns_max_three(): void
    {
        $author = User::factory()->create();

        $current = Post::create([
            'title' => 'Huidig artikel',
            'slug' => 'huidig-artikel',
            'content_html' => '<p>inhoud</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        foreach (range(1, 4) as $i) {
            Post::create([
                'title' => "Artikel {$i}",
                'slug' => "artikel-{$i}",
                'content_html' => '<p>inhoud</p>',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => now()->subDays($i + 1),
                'author_id' => $author->id,
            ]);
        }

        $response = $this->get(route('blog.show', $current->slug));

        $related = $response->viewData('page')['props']['related'];
        $this->assertCount(3, $related);
        $this->assertNotContains('huidig-artikel', array_column($related, 'slug'));
    }

    public function test_show_404s_for_draft_post(): void
    {
        $author = User::factory()->create();

        Post::create([
            'title' => 'Concept',
            'slug' => 'concept',
            'content_html' => '<p>wip</p>',
            'status' => Post::STATUS_DRAFT,
            'author_id' => $author->id,
        ]);

        $this->get(route('blog.show', 'concept'))->assertNotFound();
    }

    public function test_show_404s_for_unknown_slug(): void
    {
        $this->get(route('blog.show', 'bestaat-niet'))->assertNotFound();
    }

    public function test_show_returns_content_html_as_is(): void
    {
        $author = User::factory()->create();
        $html = '<p>Eerste alinea.</p><ul><li>Item één</li><li>Item twee</li></ul>';

        Post::create([
            'title' => 'HTML test',
            'slug' => 'html-test',
            'content_html' => $html,
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $response = $this->get(route('blog.show', 'html-test'));
        $this->assertSame($html, $response->viewData('page')['props']['post']['content_html']);
    }

    public function test_markdown_content_is_rendered_as_html(): void
    {
        $author = User::factory()->create();

        Post::create([
            'title' => 'Markdown-post',
            'slug' => 'markdown-post',
            'content_html' => "## De forfaitaire methode\nTekst met <script>alert(1)</script> erin.",
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $this->get(route('blog.show', 'markdown-post'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('post.content_html', fn (string $html) => str_contains($html, '<h2>De forfaitaire methode</h2>')
                    && ! str_contains($html, '##')
                    && ! str_contains($html, '<script>'))
            );
    }

    public function test_html_content_is_passed_through_unchanged(): void
    {
        $author = User::factory()->create();

        Post::create([
            'title' => 'HTML-post',
            'slug' => 'html-post',
            'content_html' => '<h2>Kop</h2><p>Tekst</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $this->get(route('blog.show', 'html-post'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('post.content_html', '<h2>Kop</h2><p>Tekst</p>'));
    }

    public function test_html_content_is_stripped_of_scripts_event_handlers_and_javascript_links(): void
    {
        $author = User::factory()->create();

        Post::create([
            'title' => 'Onveilige post',
            'slug' => 'onveilige-post',
            'content_html' => '<h2>Kop</h2><p onclick="steal()">Tekst<script>alert(1)</script></p>'
                .'<p><a href="javascript:alert(1)">klik</a> <a href="/tarieven">tarieven</a></p>'
                .'<table><tr><td>BPM</td></tr></table><iframe src="https://evil.example"></iframe>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $html = $this->get(route('blog.show', 'onveilige-post'))
            ->assertOk()
            ->viewData('page')['props']['post']['content_html'];

        $this->assertStringContainsString('<h2>Kop</h2>', $html);
        $this->assertStringContainsString('<a href="/tarieven">tarieven</a>', $html);
        $this->assertStringContainsString('<td>BPM</td>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    public function test_blog_index_uses_its_own_seo_title(): void
    {
        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('<title>'.e(config('seo.routes')['blog.index']['title']).'</title>', false);
    }
}
