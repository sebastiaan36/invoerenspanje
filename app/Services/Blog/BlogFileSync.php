<?php

declare(strict_types=1);

namespace App\Services\Blog;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Syncs blog posts written as Markdown files into the posts table.
 *
 * Each file in the content directory is one post. The file name (without
 * .md) is the slug. A front matter block holds the metadata:
 *
 *     ---
 *     title: "Titel van het artikel"
 *     excerpt: "Korte samenvatting"
 *     author: Sebastiaan
 *     published_at: 2026-05-23 16:40
 *     updated_at: 2026-10-25
 *     status: published
 *     hero_image: images/slug.webp
 *     ---
 *
 * Files are the source of truth: synced posts are marked `source = file`,
 * and a post whose file was removed is archived on the next sync.
 */
final class BlogFileSync
{
    public function __construct(private readonly string $contentPath) {}

    /**
     * @return array{created: list<string>, updated: list<string>, unchanged: list<string>, archived: list<string>}
     */
    public function sync(bool $dryRun = false): array
    {
        $result = ['created' => [], 'updated' => [], 'unchanged' => [], 'archived' => []];
        $slugs = [];

        foreach ($this->files() as $file) {
            $slug = basename($file, '.md');
            $slugs[] = $slug;
            $parsed = self::parse((string) file_get_contents($file), $file);

            $post = Post::query()->where('slug', $slug)->first();
            $attributes = $this->attributes($slug, $parsed, $post);

            if ($post === null) {
                $result['created'][] = $slug;
                $post = new Post(['slug' => $slug]);
            } elseif ($this->isUnchanged($post, $attributes)) {
                $result['unchanged'][] = $slug;

                continue;
            } else {
                $result['updated'][] = $slug;
            }

            if ($dryRun) {
                continue;
            }

            $post->forceFill($attributes);
            $post->timestamps = false;
            $post->created_at ??= $attributes['updated_at'];
            $post->save();
        }

        $orphans = Post::query()
            ->where('source', 'file')
            ->whereNotIn('slug', $slugs)
            ->where('status', '!=', Post::STATUS_ARCHIVED)
            ->get();

        foreach ($orphans as $orphan) {
            $result['archived'][] = $orphan->slug;

            if (! $dryRun) {
                $orphan->forceFill(['status' => Post::STATUS_ARCHIVED])->save();
            }
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    private function files(): array
    {
        if (! is_dir($this->contentPath)) {
            return [];
        }

        $files = glob(rtrim($this->contentPath, '/').'/*.md') ?: [];
        sort($files);

        return $files;
    }

    /**
     * Split a file into front matter and Markdown body.
     *
     * Deliberately a small parser (one `key: value` per line, optional quotes)
     * so production doesn't depend on a YAML package.
     *
     * @return array{meta: array<string, string>, body: string}
     */
    public static function parse(string $contents, string $file = 'bestand'): array
    {
        $contents = str_replace("\r\n", "\n", $contents);

        if (! preg_match('/\A---\n(.*?)\n---\n?(.*)\z/s', $contents, $match)) {
            throw new RuntimeException("{$file}: front matter (--- ... ---) ontbreekt.");
        }

        $meta = [];
        foreach (explode("\n", $match[1]) as $line) {
            if (trim($line) === '' || str_starts_with(ltrim($line), '#')) {
                continue;
            }

            if (! preg_match('/^([a-z_]+):\s*(.*)$/', $line, $pair)) {
                throw new RuntimeException("{$file}: ongeldige regel in front matter: {$line}");
            }

            $value = trim($pair[2]);
            if (preg_match('/^"(.*)"$/s', $value, $quoted)) {
                $value = str_replace(['\\"', '\\\\'], ['"', '\\'], $quoted[1]);
            } elseif (preg_match("/^'(.*)'$/s", $value, $quoted)) {
                $value = str_replace("''", "'", $quoted[1]);
            }

            $meta[$pair[1]] = $value;
        }

        foreach (['title', 'published_at'] as $required) {
            if (($meta[$required] ?? '') === '') {
                throw new RuntimeException("{$file}: '{$required}' ontbreekt in de front matter.");
            }
        }

        return ['meta' => $meta, 'body' => trim($match[2])];
    }

    /**
     * @param  array{meta: array<string, string>, body: string}  $parsed
     * @return array<string, mixed>
     */
    private function attributes(string $slug, array $parsed, ?Post $post): array
    {
        $meta = $parsed['meta'];
        $status = $meta['status'] ?? Post::STATUS_PUBLISHED;

        if (! in_array($status, [Post::STATUS_PUBLISHED, Post::STATUS_DRAFT], true)) {
            throw new RuntimeException("{$slug}: status moet 'published' of 'draft' zijn.");
        }

        $publishedAt = Carbon::parse($meta['published_at'], config('app.timezone'));
        $updatedAt = isset($meta['updated_at']) ? Carbon::parse($meta['updated_at'], config('app.timezone')) : $publishedAt;

        return [
            'title' => $meta['title'],
            'excerpt' => $meta['excerpt'] ?? null,
            'content_html' => Str::markdown($parsed['body'], ['html_input' => 'strip', 'allow_unsafe_links' => false]),
            'status' => $status,
            'source' => 'file',
            'published_at' => $publishedAt,
            'updated_at' => $updatedAt,
            'author_id' => $this->authorId($slug, $meta['author'] ?? null, $post),
            'hero_image_path' => $this->heroImagePath($slug, $meta['hero_image'] ?? null, $post),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function isUnchanged(Post $post, array $attributes): bool
    {
        foreach ($attributes as $key => $value) {
            $current = $post->getAttribute($key);

            if ($value instanceof Carbon) {
                if ($current === null || ! $value->equalTo($current)) {
                    return false;
                }

                continue;
            }

            if ($current !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Match the `author` name against users (case-insensitive prefix, so
     * "Sebastiaan" matches "Sebastiaan Been"); fall back to the post's current
     * author, then to the first admin.
     */
    private function authorId(string $slug, ?string $name, ?Post $post): int
    {
        $user = $name
            ? User::query()->whereRaw('LOWER(name) LIKE ?', [Str::lower($name).'%'])->orderBy('id')->first()
            : null;

        $id = $user?->id
            ?? $post?->author_id
            ?? User::query()->where('role', 'admin')->orderBy('id')->value('id');

        if ($id === null) {
            throw new RuntimeException("{$slug}: geen auteur gevonden voor '{$name}' en er is geen admin-gebruiker.");
        }

        return (int) $id;
    }

    /**
     * Copy the hero image to the public disk under a content-hashed name, so
     * an unchanged image is never copied again and a replaced one gets a new
     * URL (no stale cache). Post::saving() re-encodes it as WebP.
     */
    private function heroImagePath(string $slug, ?string $relative, ?Post $post): ?string
    {
        if ($relative === null || $relative === '') {
            return null;
        }

        $source = rtrim($this->contentPath, '/').'/'.ltrim($relative, '/');

        if (! is_file($source)) {
            throw new RuntimeException("{$slug}: afbeelding {$relative} niet gevonden.");
        }

        $hash = substr((string) hash_file('sha256', $source), 0, 10);
        $extension = Str::lower(pathinfo($source, PATHINFO_EXTENSION));
        $base = "blog/heroes/{$slug}-{$hash}";
        $disk = Storage::disk('public');

        // Already synced: the optimizer stores it as .webp under the same base name.
        foreach (["{$base}.webp", "{$base}.{$extension}"] as $candidate) {
            if ($post?->hero_image_path === $candidate && $disk->exists($candidate)) {
                return $candidate;
            }
        }

        $disk->put("{$base}.{$extension}", (string) file_get_contents($source), 'public');

        return "{$base}.{$extension}";
    }
}
