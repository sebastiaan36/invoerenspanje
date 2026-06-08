<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_pages_render_their_configured_title_and_description(): void
    {
        foreach (config('seo.routes') as $routeName => $meta) {
            if ($routeName === 'blog.index') {
                continue; // covered separately with a real route below
            }

            $response = $this->get(route($routeName));

            $response->assertOk();
            $response->assertSee('<title>'.e($meta['title']).'</title>', false);
            $response->assertSee('<meta name="description" content="'.e($meta['description']).'">', false);
            $response->assertSee('<meta property="og:title" content="'.e($meta['title']).'">', false);
        }
    }

    public function test_home_page_emits_canonical_and_open_graph_tags(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta property="og:site_name" content="'.e(config('seo.site_name')).'">', false);
        $response->assertSee('<meta name="twitter:card"', false);
    }

    public function test_unconfigured_page_falls_back_to_default_meta(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('<title>'.e(config('seo.default.title')).'</title>', false);
        $response->assertSee('<meta name="description" content="'.e(config('seo.default.description')).'">', false);
    }

    public function test_blog_post_overrides_meta_with_its_own_title_and_excerpt(): void
    {
        $author = User::factory()->create();

        $post = Post::create([
            'title' => 'BPM-teruggave uitgelegd',
            'slug' => 'bpm-teruggave-uitgelegd',
            'excerpt' => 'Alles over BPM-teruggave bij export naar Spanje.',
            'content_html' => '<p>inhoud</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee('<title>'.e($post->title.' — autoinvoerenspanje.nl').'</title>', false);
        $response->assertSee('<meta name="description" content="'.e($post->excerpt).'">', false);
        $response->assertSee('<meta property="og:title" content="'.e($post->title.' — autoinvoerenspanje.nl').'">', false);
    }
}
