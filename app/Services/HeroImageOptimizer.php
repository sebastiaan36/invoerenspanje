<?php

declare(strict_types=1);

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Re-encodes uploaded blog images as WebP, scaled down to at most 1600 px
 * wide. A 2.3 MB PNG hero was the LCP element on mobile (16 s); as WebP at
 * 1600 px it is typically well under 150 KB. Smaller width variants
 * (hero-480.webp, …) are written next to it for srcset, so a phone doesn't
 * download the 1600 px version.
 */
final class HeroImageOptimizer
{
    public const MAX_WIDTH = 1600;

    public const QUALITY = 80;

    /** @var list<int> */
    public const VARIANT_WIDTHS = [480, 800, 1200];

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
            $this->writeMissingVariants($path, $source);

            return $path;
        }

        $image = $width > self::MAX_WIDTH
            ? imagescale($source, self::MAX_WIDTH)
            : $source;
        // GD bitmaps are large (4 bytes/pixel); free the source before the
        // variants are scaled so only one full-size bitmap is held at a time.
        unset($source);

        // Keep PNG transparency when re-encoding.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $newPath = preg_replace('/\.[^.\/]+$/', '', $path).'.webp';
        $disk->put($newPath, $this->encodeWebp($image), 'public');
        // A freshly converted image replaces any variants of an older upload.
        $this->writeMissingVariants($newPath, $image, overwrite: true);
        unset($image);

        if ($newPath !== $path) {
            $disk->delete($path);
        }

        return $newPath;
    }

    /**
     * The srcset for a hero on the public disk: its width variants plus the
     * image itself, or null when no variants exist (small or legacy images).
     */
    public function srcset(string $path): ?string
    {
        $disk = Storage::disk('public');
        $candidates = [];

        foreach (self::VARIANT_WIDTHS as $variantWidth) {
            $variantPath = $this->variantPath($path, $variantWidth);

            if ($disk->exists($variantPath)) {
                $candidates[] = asset('storage/'.$variantPath)." {$variantWidth}w";
            }
        }

        if ($candidates === []) {
            return null;
        }

        $size = @getimagesize($disk->path($path));
        $candidates[] = asset('storage/'.$path).' '.($size[0] ?? self::MAX_WIDTH).'w';

        return implode(', ', $candidates);
    }

    public function variantPath(string $path, int $width): string
    {
        return preg_replace('/\.[^.\/]+$/', '', $path)."-{$width}.webp";
    }

    /**
     * Write the width variants that are narrower than the image. Existing
     * variants are kept unless $overwrite, so running the optimizer again on
     * an unchanged image is cheap.
     */
    private function writeMissingVariants(string $path, GdImage $image, bool $overwrite = false): void
    {
        $disk = Storage::disk('public');
        $width = imagesx($image);

        foreach (self::VARIANT_WIDTHS as $variantWidth) {
            $variantPath = $this->variantPath($path, $variantWidth);

            if ($variantWidth >= $width || (! $overwrite && $disk->exists($variantPath))) {
                continue;
            }

            $variant = imagescale($image, $variantWidth);
            imagealphablending($variant, true);
            imagesavealpha($variant, true);
            $disk->put($variantPath, $this->encodeWebp($variant), 'public');
            unset($variant);
        }
    }

    private function encodeWebp(GdImage $image): string
    {
        ob_start();
        imagewebp($image, null, self::QUALITY);

        return (string) ob_get_clean();
    }
}
