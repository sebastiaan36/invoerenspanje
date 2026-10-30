<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InstagramPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_the_hand_picked_articles_in_order_and_skips_others(): void
    {
        $this->createPost('wanneer-auto-op-spaans-kenteken-verplicht', 'Wanneer verplicht?');
        $this->createPost('kosten-auto-invoeren-spanje-rekenvoorbeelden', 'Wat kost het?');
        $this->createPost('niet-geselecteerd-artikel', 'Niet op de pagina');
        $this->createPost('bpm-teruggave-auto-importeren-spanje', 'Concept', status: Post::STATUS_DRAFT);

        $this->get(route('instagram'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Instagram')
                ->has('posts', 2)
                ->where('posts.0.slug', 'kosten-auto-invoeren-spanje-rekenvoorbeelden')
                ->where('posts.1.slug', 'wanneer-auto-op-spaans-kenteken-verplicht')
                ->where('posts.0.title', 'Wat kost het?')
            );
    }

    public function test_it_is_kept_out_of_search_results_and_the_sitemap(): void
    {
        $html = $this->get('/instagram')->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringContainsString('<title>Auto op Spaans kenteken — autoinvoerenspanje.nl</title>', $html);
        $this->assertStringNotContainsString('/instagram', $this->get(route('sitemap'))->getContent());
    }

    private function createPost(string $slug, string $title, string $status = Post::STATUS_PUBLISHED): void
    {
        Post::create([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => 'Samenvatting.',
            'content_html' => '<p>Tekst.</p>',
            'status' => $status,
            'published_at' => now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);
    }
}
