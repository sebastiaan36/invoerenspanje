<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\HeroImageOptimizer;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $content_html
 * @property string|null $hero_image_path
 * @property string $status
 * @property CarbonInterface|null $published_at
 * @property int $author_id
 */
final class Post extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    protected $guarded = ['id'];

    /**
     * New or replaced hero images are re-encoded as WebP before saving, so
     * oversized uploads never reach the public blog.
     */
    protected static function booted(): void
    {
        self::saving(function (Post $post): void {
            if ($post->isDirty('hero_image_path') && $post->hero_image_path) {
                $post->hero_image_path = app(HeroImageOptimizer::class)->optimize($post->hero_image_path);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Synced from a Markdown file in resources/content/blog; edit the file,
     * not the record (the next `blog:sync` would overwrite admin edits).
     */
    public function isFileManaged(): bool
    {
        return $this->source === 'file';
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
