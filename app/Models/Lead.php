<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $woonplaats_spanje
 * @property string|null $expected_move_date
 * @property string|null $comment
 * @property string $kenteken
 * @property string $package_slug
 * @property bool $residency_change
 * @property string $autonomia
 * @property int|null $bpm_teruggave_indicatie_eur
 * @property int|null $import_kosten_indicatie_eur
 * @property int|null $totaalprijs_indicatie_eur
 * @property string $source
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 * @property string $status
 * @property Carbon|null $lost_at
 * @property string|null $feedback_token
 * @property Carbon|null $feedback_requested_at
 */
final class Lead extends Model
{
    use HasFactory;

    public const string STATUS_LOST = 'verloren';

    /** Aantal dagen na 'verloren' waarna we om feedback vragen. */
    public const int FEEDBACK_DELAY_DAYS = 14;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        // Houdt bij wanneer een lead verloren is, ongeacht waar de status wordt
        // gewijzigd (detailpagina, tabel of code).
        self::saving(function (Lead $lead): void {
            if (! $lead->isDirty('status')) {
                return;
            }

            $lead->lost_at = $lead->status === self::STATUS_LOST ? now() : null;
        });
    }

    /**
     * Verloren leads die 2 weken geleden (of eerder) verloren zijn en nog geen feedbackmail kregen.
     *
     * @param  Builder<Lead>  $query
     */
    #[Scope]
    protected function dueForFeedbackRequest(Builder $query): void
    {
        $query->where('status', self::STATUS_LOST)
            ->whereNotNull('lost_at')
            ->where('lost_at', '<=', now()->subDays(self::FEEDBACK_DELAY_DAYS))
            ->whereNull('feedback_requested_at');
    }

    /**
     * Unieke, niet te raden token voor de persoonlijke feedbacklink.
     */
    public function ensureFeedbackToken(): string
    {
        if ($this->feedback_token === null) {
            $this->feedback_token = Str::random(48);
            $this->save();
        }

        return $this->feedback_token;
    }

    public function feedbackUrl(): string
    {
        return route('feedback.show', ['token' => $this->ensureFeedbackToken()]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lost_at' => 'datetime',
            'feedback_requested_at' => 'datetime',
            'residency_change' => 'boolean',
            'bpm_teruggave_indicatie_eur' => 'integer',
            'import_kosten_indicatie_eur' => 'integer',
            'totaalprijs_indicatie_eur' => 'integer',
            'rdw_snapshot_json' => 'array',
            'bpm_calculation_json' => 'array',
            'import_calculation_json' => 'array',
        ];
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * @return HasMany<LeadFeedback, $this>
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(LeadFeedback::class)->latest();
    }
}
