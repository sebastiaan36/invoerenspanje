<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dossier_id
 * @property string $type
 * @property string $filename
 * @property string $path
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $status
 * @property string|null $review_note
 * @property int|null $reviewed_by
 * @property \Carbon\CarbonInterface|null $reviewed_at
 */
final class Document extends Model
{
    use HasFactory;

    public const STATUS_AANGEVRAAGD = 'aangevraagd';

    public const STATUS_GEUPLOAD = 'geupload';

    public const STATUS_GOEDGEKEURD = 'goedgekeurd';

    public const STATUS_AFGEKEURD = 'afgekeurd';

    public const TYPES = [
        'paspoort' => 'Paspoort / ID-kaart',
        'nie' => 'NIE-document',
        'kentekenbewijs' => 'Kentekenbewijs (NL)',
        'coc' => 'CoC / certificaat van conformiteit',
        'overig' => 'Overig',
    ];

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'size_bytes' => 'integer',
        ];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
