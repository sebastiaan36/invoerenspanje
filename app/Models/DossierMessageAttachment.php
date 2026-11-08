<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $dossier_message_id
 * @property string $filename
 * @property string $path
 * @property string $mime_type
 * @property int $size_bytes
 */
final class DossierMessageAttachment extends Model
{
    use HasFactory;

    protected $table = 'dossier_message_attachments';

    protected $guarded = ['id'];

    /**
     * Remove the stored file together with the record, once the delete is committed.
     */
    protected static function booted(): void
    {
        self::deleted(function (DossierMessageAttachment $attachment): void {
            $path = $attachment->path;

            DB::afterCommit(fn () => Storage::disk('local')->delete($path));
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(DossierMessage::class, 'dossier_message_id');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
