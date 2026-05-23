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
}
