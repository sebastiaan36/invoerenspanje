<?php

declare(strict_types=1);

namespace Tests\Feature\Blog;

use App\Models\Post;
use App\Models\User;
use App\Services\Blog\UnpublishedBlogLinkFilter;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class UnpublishedBlogLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_link_to_a_scheduled_post_is_plain_text_until_it_is_published(): void
    {
        $this->createPost('gepubliceerd', '<p>Lees <a href="/blog/ingepland">over de trekhaak</a> en <a href="/blog/ook-live">over de ITV</a>.</p>', now()->subDay());
        $this->createPost('ook-live', '<p>x</p>', now()->subDay());
        $this->createPost('ingepland', '<p>x</p>', now()->addWeek());

        $this->get(route('blog.show', 'gepubliceerd'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('post.content_html', '<p>Lees over de trekhaak en <a href="/blog/ook-live">over de ITV</a>.</p>'));

        $this->travel(8)->days();

        $this->get(route('blog.show', 'gepubliceerd'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('post.content_html', fn (string $html) => str_contains($html, '<a href="/blog/ingepland">over de trekhaak</a>')));
    }

    public function test_the_filter_handles_absolute_urls_drafts_and_unknown_posts(): void
    {
        $this->createPost('live', '<p>x</p>', now()->subDay());
        $this->createPost('concept', '<p>x</p>', now()->subDay(), Post::STATUS_DRAFT);

        $html = '<a href="https://autoinvoerenspanje.nl/blog/live">A</a> <a href="/blog/concept">B</a> <a href="/blog/bestaat-niet/">C</a> <a href="/tarieven">D</a>';

        $this->assertSame(
            '<a href="https://autoinvoerenspanje.nl/blog/live">A</a> B C <a href="/tarieven">D</a>',
            app(UnpublishedBlogLinkFilter::class)->filter($html),
        );
    }

    private function createPost(string $slug, string $html, DateTimeInterface $publishedAt, string $status = Post::STATUS_PUBLISHED): void
    {
        Post::create([
            'title' => ucfirst($slug),
            'slug' => $slug,
            'content_html' => $html,
            'status' => $status,
            'published_at' => $publishedAt,
            'author_id' => User::factory()->create()->id,
        ]);
    }
}
