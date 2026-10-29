<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A real customer review, managed in the admin and shown on the homepage
 * once published. Only enter reviews the customer actually gave (with their
 * permission), e.g. copied from Google reviews.
 *
 * @property int $id
 * @property string $name
 * @property string|null $location
 * @property int $rating
 * @property string $text
 * @property string $source
 * @property CarbonInterface $reviewed_on
 * @property bool $is_published
 * @property int $sort_order
 */
final class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'reviewed_on' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<Review>  $query
     * @return Builder<Review>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('reviewed_on');
    }

    /**
     * Shape used by ReviewsSection.vue (same as the config examples).
     *
     * @return array{name: string, location: string, rating: int, date: string, text: string, source: string, is_example: bool}
     */
    public function toHomepageArray(): array
    {
        return [
            'name' => $this->name,
            'location' => (string) $this->location,
            'rating' => $this->rating,
            'date' => $this->reviewed_on->format('Y-m'),
            'text' => $this->text,
            'source' => $this->source,
            'is_example' => false,
        ];
    }
}
