<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InstagramPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_the_hand_picked_articles_in_order_and_skips_others(): void
    {
        $this->createPost('nieuwste-artikel', 'Nieuwste', now()->subHour());
        $this->createPost('wanneer-auto-op-spaans-kenteken-verplicht', 'Wanneer verplicht?');
        $this->createPost('kosten-auto-invoeren-spanje-rekenvoorbeelden', 'Wat kost het?');
        $this->createPost('niet-geselecteerd-artikel', 'Niet op de pagina');
        $this->createPost('bpm-teruggave-auto-importeren-spanje', 'Concept', status: Post::STATUS_DRAFT);

        $this->get(route('instagram'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Instagram')
                ->has('posts', 3)
                ->where('posts.1.slug', 'kosten-auto-invoeren-spanje-rekenvoorbeelden')
                ->where('posts.2.slug', 'wanneer-auto-op-spaans-kenteken-verplicht')
                ->where('posts.1.title', 'Wat kost het?')
                ->where('posts.1.is_newest', false)
            );
    }

    public function test_the_newest_published_post_is_always_on_top(): void
    {
        $this->createPost('kosten-auto-invoeren-spanje-rekenvoorbeelden', 'Wat kost het?', now()->subWeek());
        $this->createPost('nieuwe-blog', 'Nieuwe blog', now()->subHour());
        $this->createPost('ingeplande-blog', 'Ingepland', now()->addWeek());

        $this->get(route('instagram'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('posts', 2)
                ->where('posts.0.slug', 'nieuwe-blog')
                ->where('posts.0.is_newest', true)
                ->where('posts.1.slug', 'kosten-auto-invoeren-spanje-rekenvoorbeelden')
            );

        $this->travel(8)->days();

        $this->get(route('instagram'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('posts.0.slug', 'ingeplande-blog')
                ->where('posts.1.slug', 'kosten-auto-invoeren-spanje-rekenvoorbeelden')
            );
    }

    public function test_a_hand_picked_newest_post_is_not_listed_twice(): void
    {
        $this->createPost('kosten-auto-invoeren-spanje-rekenvoorbeelden', 'Wat kost het?', now()->subHour());
        $this->createPost('wanneer-auto-op-spaans-kenteken-verplicht', 'Wanneer verplicht?', now()->subWeek());

        $this->get(route('instagram'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('posts', 2)
                ->where('posts.0.slug', 'kosten-auto-invoeren-spanje-rekenvoorbeelden')
                ->where('posts.0.is_newest', true)
                ->where('posts.1.slug', 'wanneer-auto-op-spaans-kenteken-verplicht')
            );
    }

    public function test_it_is_kept_out_of_search_results_and_the_sitemap(): void
    {
        $html = $this->get('/instagram')->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringContainsString('<title>Auto op Spaans kenteken — autoinvoerenspanje.nl</title>', $html);
        $this->assertStringNotContainsString('/instagram', $this->get(route('sitemap'))->getContent());
    }

    private function createPost(string $slug, string $title, ?DateTimeInterface $publishedAt = null, string $status = Post::STATUS_PUBLISHED): void
    {
        Post::create([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => 'Samenvatting.',
            'content_html' => '<p>Tekst.</p>',
            'status' => $status,
            'published_at' => $publishedAt ?? now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);
    }
}
