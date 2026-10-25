<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\HeroImageOptimizer;
use Illuminate\Console\Command;

final class OptimizeBlogImages extends Command
{
    protected $signature = 'blog:optimize-images';

    protected $description = 'Converteer bestaande blogafbeeldingen naar WebP (max. 1600 px breed)';

    public function handle(HeroImageOptimizer $optimizer): int
    {
        $posts = Post::query()->whereNotNull('hero_image_path')->get();

        foreach ($posts as $post) {
            $original = $post->hero_image_path;
            $optimized = $optimizer->optimize($original);

            if ($optimized !== $original) {
                $post->forceFill(['hero_image_path' => $optimized])->saveQuietly();
                $this->line("{$original} → {$optimized}");
            }
        }

        $this->info("{$posts->count()} afbeelding(en) gecontroleerd.");

        return self::SUCCESS;
    }
}
