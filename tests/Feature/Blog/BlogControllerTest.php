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
            'content_markdown' => 'wip',
            'status' => Post::STATUS_DRAFT,
            'author_id' => $author->id,
        ]);

        Post::create([
            'title' => 'Gearchiveerd',
            'slug' => 'gearchiveerd',
            'content_markdown' => 'oud',
            'status' => Post::STATUS_ARCHIVED,
            'published_at' => now()->subYear(),
            'author_id' => $author->id,
        ]);

        Post::create([
            'title' => 'Toekomstig',
            'slug' => 'toekomstig',
            'content_markdown' => 'later',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->addDay(),
            'author_id' => $author->id,
        ]);

        Post::create([
            'title' => 'Live artikel',
            'slug' => 'live-artikel',
            'excerpt' => 'Korte intro',
            'content_markdown' => 'inhoud',
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

    public function test_show_renders_published_post_and_compiles_markdown(): void
    {
        $author = User::factory()->create(['name' => 'Sebastiaan']);

        Post::create([
            'title' => 'Markdown rendering',
            'slug' => 'markdown-rendering',
            'excerpt' => 'Test',
            'content_markdown' => "## Kopje\n\nMet **vetgedrukte** tekst en een [link](https://example.com).",
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $response = $this->get(route('blog.show', 'markdown-rendering'));

        $response->assertOk();
        $post = $response->viewData('page')['props']['post'];
        $this->assertSame('markdown-rendering', $post['slug']);
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
            'content_markdown' => 'wip',
            'status' => Post::STATUS_DRAFT,
            'author_id' => $author->id,
        ]);

        $this->get(route('blog.show', 'concept'))->assertNotFound();
    }

    public function test_show_404s_for_unknown_slug(): void
    {
        $this->get(route('blog.show', 'bestaat-niet'))->assertNotFound();
    }

    public function test_show_strips_raw_html_from_markdown(): void
    {
        $author = User::factory()->create();

        Post::create([
            'title' => 'XSS test',
            'slug' => 'xss-test',
            'content_markdown' => "Hello\n\n<script>alert('xss')</script>\n\n<p>Allowed paragraph?</p>",
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $response = $this->get(route('blog.show', 'xss-test'));
        $html = $response->viewData('page')['props']['post']['content_html'];
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<p>Allowed paragraph?</p>', $html);
    }
}
