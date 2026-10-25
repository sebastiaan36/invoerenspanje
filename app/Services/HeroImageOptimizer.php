<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Re-encodes uploaded blog images as WebP, scaled down to at most 1600 px
 * wide. A 2.3 MB PNG hero was the LCP element on mobile (16 s); as WebP at
 * 1600 px it is typically well under 150 KB.
 */
final class HeroImageOptimizer
{
    public const MAX_WIDTH = 1600;

    public const QUALITY = 80;

    /**
     * Optimise the image at $path on the public disk and return the new path,
     * or the original path when the file can't be read or is already WebP
     * within the size limit.
     */
    public function optimize(string $path): string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return $path;
        }

        $source = @imagecreatefromstring($disk->get($path));

        if ($source === false) {
            return $path;
        }

        $width = imagesx($source);
        $alreadyOptimal = Str::endsWith(Str::lower($path), '.webp') && $width <= self::MAX_WIDTH;

        if ($alreadyOptimal) {
            imagedestroy($source);

            return $path;
        }

        $image = $width > self::MAX_WIDTH
            ? imagescale($source, self::MAX_WIDTH)
            : $source;

        // Keep PNG transparency when re-encoding.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::QUALITY);
        $webp = (string) ob_get_clean();

        if ($image !== $source) {
            imagedestroy($image);
        }
        imagedestroy($source);

        $newPath = preg_replace('/\.[^.\/]+$/', '', $path).'.webp';
        $disk->put($newPath, $webp, 'public');

        if ($newPath !== $path) {
            $disk->delete($path);
        }

        return $newPath;
    }
}
