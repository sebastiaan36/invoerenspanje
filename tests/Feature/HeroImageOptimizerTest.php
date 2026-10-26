<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Services\HeroImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroImageOptimizerTest extends TestCase
{
    use RefreshDatabase;

    private function storePng(string $path, int $width, int $height): void
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 90));
        ob_start();
        imagepng($image);
        Storage::disk('public')->put($path, (string) ob_get_clean());
        imagedestroy($image);
    }

    public function test_large_png_becomes_scaled_webp_and_original_is_removed(): void
    {
        Storage::fake('public');
        $this->storePng('blog/heroes/hero.png', 2400, 1350);

        $newPath = app(HeroImageOptimizer::class)->optimize('blog/heroes/hero.png');

        $this->assertSame('blog/heroes/hero.webp', $newPath);
        Storage::disk('public')->assertMissing('blog/heroes/hero.png');
        [$width, , $type] = getimagesizefromstring(Storage::disk('public')->get($newPath));
        $this->assertSame(HeroImageOptimizer::MAX_WIDTH, $width);
        $this->assertSame(IMAGETYPE_WEBP, $type);
    }

    public function test_width_variants_are_written_for_srcset(): void
    {
        Storage::fake('public');
        $this->storePng('blog/heroes/hero.png', 2400, 1350);
        $optimizer = app(HeroImageOptimizer::class);

        $path = $optimizer->optimize('blog/heroes/hero.png');

        foreach (HeroImageOptimizer::VARIANT_WIDTHS as $width) {
            $variant = $optimizer->variantPath($path, $width);
            Storage::disk('public')->assertExists($variant);
            $this->assertSame($width, getimagesizefromstring(Storage::disk('public')->get($variant))[0]);
        }

        $srcset = (string) $optimizer->srcset($path);
        $this->assertStringContainsString('hero-480.webp 480w', $srcset);
        $this->assertStringContainsString('hero.webp 1600w', $srcset);
    }

    public function test_no_variants_wider_than_the_image_and_no_srcset_without_variants(): void
    {
        Storage::fake('public');
        $this->storePng('blog/heroes/small.png', 600, 340);
        $optimizer = app(HeroImageOptimizer::class);

        $path = $optimizer->optimize('blog/heroes/small.png');

        Storage::disk('public')->assertExists('blog/heroes/small-480.webp');
        Storage::disk('public')->assertMissing('blog/heroes/small-800.webp');
        $this->assertStringContainsString('small.webp 600w', (string) $optimizer->srcset($path));
        $this->assertNull($optimizer->srcset('blog/heroes/unknown.webp'));
    }

    public function test_existing_webp_heroes_get_their_variants_backfilled(): void
    {
        Storage::fake('public');
        $image = imagecreatetruecolor(1600, 900);
        ob_start();
        imagewebp($image);
        Storage::disk('public')->put('blog/heroes/legacy.webp', (string) ob_get_clean());

        $path = app(HeroImageOptimizer::class)->optimize('blog/heroes/legacy.webp');

        $this->assertSame('blog/heroes/legacy.webp', $path);
        Storage::disk('public')->assertExists('blog/heroes/legacy-1200.webp');
    }

    public function test_missing_file_is_left_untouched(): void
    {
        Storage::fake('public');

        $this->assertSame('blog/heroes/none.png', app(HeroImageOptimizer::class)->optimize('blog/heroes/none.png'));
    }

    public function test_saving_a_post_with_a_new_hero_optimizes_it(): void
    {
        Storage::fake('public');
        $this->storePng('blog/heroes/upload.png', 800, 450);

        $post = Post::create([
            'title' => 'Test',
            'slug' => 'test',
            'content_html' => '<p>x</p>',
            'status' => Post::STATUS_DRAFT,
            'author_id' => User::factory()->create()->id,
            'hero_image_path' => 'blog/heroes/upload.png',
        ]);

        $this->assertSame('blog/heroes/upload.webp', $post->hero_image_path);
        Storage::disk('public')->assertExists('blog/heroes/upload.webp');
    }

    public function test_command_converts_existing_heroes(): void
    {
        Storage::fake('public');
        $post = Post::create([
            'title' => 'Oud',
            'slug' => 'oud',
            'content_html' => '<p>x</p>',
            'status' => Post::STATUS_DRAFT,
            'author_id' => User::factory()->create()->id,
        ]);
        $this->storePng('blog/heroes/oud.png', 1800, 1000);
        $post->forceFill(['hero_image_path' => 'blog/heroes/oud.png'])->saveQuietly();

        $this->artisan('blog:optimize-images')->assertSuccessful();

        $this->assertSame('blog/heroes/oud.webp', $post->fresh()->hero_image_path);
    }
}
