<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $dossier_id
 * @property int $author_id
 * @property string $author_role  klant|admin
 * @property string $body
 * @property \Carbon\CarbonInterface|null $read_at
 * @property \Carbon\CarbonInterface|null $notified_at
 */
final class DossierMessage extends Model
{
    use HasFactory;

    protected $table = 'dossier_messages';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::created(function (DossierMessage $message): void {
            // Plan een mail naar de tegenpartij — throttle naar max 1 mail per uur per dossier+richting.
            app(\App\Services\Messages\MessageNotifier::class)->onCreated($message);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DossierMessageAttachment::class);
    }
}
