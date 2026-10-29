<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class HomepageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_real_reviews_the_homepage_shows_none(): void
    {
        config(['reviews.show_examples' => false]);

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('reviews', []));
    }

    public function test_example_reviews_are_flagged_when_switched_on_outside_production(): void
    {
        config(['reviews.show_examples' => true]);

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('reviews', count(config('reviews.examples')))
                ->where('reviews.0.is_example', true));
    }

    public function test_example_reviews_never_show_in_production_even_when_switched_on(): void
    {
        config(['reviews.show_examples' => true]);
        $this->app->detectEnvironment(fn () => 'production');

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('reviews', []));
    }

    public function test_only_published_reviews_are_shown_in_their_sort_order(): void
    {
        config(['reviews.show_examples' => false]);
        Review::factory()->published()->create(['name' => 'Tweede', 'sort_order' => 2, 'reviewed_on' => '2026-09-14']);
        Review::factory()->published()->create(['name' => 'Eerste', 'sort_order' => 1]);
        Review::factory()->create(['name' => 'Concept']);

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('reviews', 2)
                ->where('reviews.0.name', 'Eerste')
                ->where('reviews.1.name', 'Tweede')
                ->where('reviews.1.date', '2026-09')
                ->where('reviews.1.is_example', false));
    }

    public function test_examples_come_after_real_reviews_in_the_preview(): void
    {
        config(['reviews.show_examples' => true]);
        Review::factory()->published()->create(['name' => 'Echte klant']);

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('reviews.0.name', 'Echte klant')
                ->where('reviews.0.is_example', false)
                ->where('reviews.1.is_example', true));
    }

    public function test_cost_overview_uses_the_configured_prices_with_non_breaking_spaces(): void
    {
        $html = $this->get(route('home'))->viewData('page')['props']['contentHtml'];

        $this->assertStringContainsString('vanaf €&nbsp;395', $html);
        $this->assertStringContainsString('ca. €&nbsp;730', $html);
        $this->assertStringNotContainsString('€ 6.000', $html);
    }

    public function test_recent_posts_list_up_to_three_published_posts_with_a_hero(): void
    {
        $author = User::factory()->create();
        foreach (range(1, 4) as $i) {
            Post::create([
                'title' => "Artikel {$i}",
                'slug' => "artikel-{$i}",
                'content_html' => '<p>x</p>',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => now()->subDays($i),
                'author_id' => $author->id,
            ])->forceFill(['hero_image_path' => "blog/heroes/artikel-{$i}.webp"])->saveQuietly();
        }
        Post::create([
            'title' => 'Zonder afbeelding',
            'slug' => 'zonder-afbeelding',
            'content_html' => '<p>x</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
            'author_id' => $author->id,
        ]);

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('recentPosts', 3)
                ->where('recentPosts.0.slug', 'artikel-1'));
    }
}
