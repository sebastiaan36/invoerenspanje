<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $lead_id
 * @property string $status
 * @property string $kenteken
 * @property string|null $merk
 * @property string|null $model
 * @property \Carbon\CarbonInterface|null $datum_eerste_toelating
 * @property string|null $brandstof
 * @property int|null $co2
 * @property array<string, mixed>|null $rdw_data_json
 * @property string $pakket
 * @property int|null $bpm_indicatie_eur
 * @property int|null $service_fee_eur
 * @property \Carbon\CarbonInterface|null $started_at
 * @property \Carbon\CarbonInterface|null $completed_at
 */
final class Dossier extends Model
{
    use HasFactory;

    public const STATUS_CONCEPT = 'concept';

    public const STATUS_OFFERTE = 'offerte';

    public const STATUS_AKKOORD = 'akkoord';

    public const STATUS_IN_UITVOERING = 'in_uitvoering';

    public const STATUS_AFGEROND = 'afgerond';

    public const STATUS_GEANNULEERD = 'geannuleerd';

    /** Volgorde van fasen in de timeline (zonder geannuleerd). */
    public const TIMELINE_PHASES = [
        self::STATUS_OFFERTE,
        self::STATUS_AKKOORD,
        self::STATUS_IN_UITVOERING,
        self::STATUS_AFGEROND,
    ];

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'datum_eerste_toelating' => 'date',
            'rdw_data_json' => 'array',
            'bpm_calculation_json' => 'array',
            'import_calculation_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DossierMessage::class);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_GEANNULEERD;
    }
}
