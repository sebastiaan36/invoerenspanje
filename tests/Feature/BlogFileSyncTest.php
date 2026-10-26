<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Services\Blog\BlogFileSync;
use App\Services\HeroImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class BlogFileSyncTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->dir = storage_path('framework/testing/blog-'.uniqid());
        File::ensureDirectoryExists($this->dir.'/images');
        $this->app->bind(BlogFileSync::class, fn () => new BlogFileSync($this->dir));

        User::factory()->create(['name' => 'Sebastiaan Been', 'role' => 'admin']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    private function writePost(string $slug, array $meta, string $body = "Intro.\n\n## Kop\n\n| A | B |\n|---|---|\n| 1 | 2 |"): void
    {
        $lines = collect($meta)->map(fn ($value, $key) => "{$key}: {$value}")->implode("\n");
        File::put("{$this->dir}/{$slug}.md", "---\n{$lines}\n---\n{$body}\n");
    }

    private function writeImage(string $name): void
    {
        $image = imagecreatetruecolor(1800, 1200);
        ob_start();
        imagepng($image);
        File::put("{$this->dir}/images/{$name}", (string) ob_get_clean());
    }

    public function test_it_creates_a_published_post_from_a_markdown_file(): void
    {
        $this->writePost('bpm-uitleg', [
            'title' => '"BPM-teruggave: zo werkt het"',
            'excerpt' => '"Korte samenvatting."',
            'author' => 'Sebastiaan',
            'published_at' => '2026-05-23 16:40',
            'updated_at' => '2026-10-25',
            'status' => 'published',
        ]);

        $this->artisan('blog:sync')->assertSuccessful();

        $post = Post::firstWhere('slug', 'bpm-uitleg');
        $this->assertSame('BPM-teruggave: zo werkt het', $post->title);
        $this->assertTrue($post->isFileManaged());
        $this->assertTrue($post->isPublished());
        $this->assertStringContainsString('<h2>Kop</h2>', $post->content_html);
        $this->assertStringContainsString('<table>', $post->content_html);
        $this->assertSame('2026-10-25', $post->updated_at->toDateString());
        $this->assertSame('Sebastiaan Been', $post->author->name);
    }

    public function test_it_takes_over_an_existing_post_with_the_same_slug(): void
    {
        $existing = Post::create([
            'title' => 'Oud',
            'slug' => 'bpm-uitleg',
            'content_html' => '<p>oud</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subMonth(),
            'author_id' => User::first()->id,
        ]);
        $this->writePost('bpm-uitleg', ['title' => 'Nieuw', 'published_at' => '2026-05-23 16:40']);

        $this->artisan('blog:sync')->assertSuccessful();

        $this->assertSame(1, Post::count());
        $this->assertSame('Nieuw', $existing->fresh()->title);
        $this->assertSame('file', $existing->fresh()->source);
    }

    public function test_a_second_sync_without_changes_touches_nothing(): void
    {
        $this->writePost('bpm-uitleg', ['title' => 'Titel', 'published_at' => '2026-05-23 16:40', 'hero_image' => 'images/hero.png']);
        $this->writeImage('hero.png');

        $sync = app(BlogFileSync::class);
        $sync->sync();
        $result = $sync->sync();

        $this->assertSame(['bpm-uitleg'], $result['unchanged']);
        // One hero plus its srcset width variants — nothing copied twice.
        $this->assertCount(1 + count(HeroImageOptimizer::VARIANT_WIDTHS), Storage::disk('public')->files('blog/heroes'));
    }

    public function test_hero_image_is_copied_and_converted_to_webp(): void
    {
        $this->writePost('bpm-uitleg', ['title' => 'Titel', 'published_at' => '2026-05-23 16:40', 'hero_image' => 'images/hero.png']);
        $this->writeImage('hero.png');

        app(BlogFileSync::class)->sync();

        $path = Post::firstWhere('slug', 'bpm-uitleg')->hero_image_path;
        $this->assertMatchesRegularExpression('#^blog/heroes/bpm-uitleg-[0-9a-f]{10}\.webp$#', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_drafts_stay_unpublished_and_dry_run_saves_nothing(): void
    {
        $this->writePost('concept', ['title' => 'Concept', 'published_at' => '2026-05-23 16:40', 'status' => 'draft']);

        $this->artisan('blog:sync --dry-run')->assertSuccessful();
        $this->assertSame(0, Post::count());

        $this->artisan('blog:sync')->assertSuccessful();
        $this->assertFalse(Post::firstWhere('slug', 'concept')->isPublished());
    }

    public function test_removing_a_file_archives_its_post_but_leaves_admin_posts_alone(): void
    {
        $this->writePost('weg', ['title' => 'Weg', 'published_at' => '2026-05-23 16:40']);
        app(BlogFileSync::class)->sync();
        Post::create([
            'title' => 'Uit admin',
            'slug' => 'uit-admin',
            'content_html' => '<p>x</p>',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'author_id' => User::first()->id,
        ]);
        File::delete("{$this->dir}/weg.md");

        $result = app(BlogFileSync::class)->sync();

        $this->assertSame(['weg'], $result['archived']);
        $this->assertSame(Post::STATUS_ARCHIVED, Post::firstWhere('slug', 'weg')->status);
        $this->assertSame(Post::STATUS_PUBLISHED, Post::firstWhere('slug', 'uit-admin')->status);
    }

    public function test_invalid_files_fail_with_a_clear_message(): void
    {
        File::put("{$this->dir}/kapot.md", "Geen front matter\n");

        $this->artisan('blog:sync')
            ->expectsOutputToContain('front matter')
            ->assertFailed();
    }

    public function test_parser_handles_quotes_and_colons(): void
    {
        $parsed = BlogFileSync::parse("---\ntitle: \"ITV: zo \\\"werkt\\\" het\"\npublished_at: 2026-06-18 17:42\n---\nTekst");

        $this->assertSame('ITV: zo "werkt" het', $parsed['meta']['title']);
        $this->assertSame('Tekst', $parsed['body']);
    }

    public function test_missing_image_is_reported(): void
    {
        $this->writePost('x', ['title' => 'X', 'published_at' => '2026-05-23 16:40', 'hero_image' => 'images/bestaat-niet.png']);

        $this->expectException(RuntimeException::class);
        app(BlogFileSync::class)->sync();
    }
}
