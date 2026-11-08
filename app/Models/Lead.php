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
use LogicException;

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
 * @property Carbon|null $won_at
 * @property string|null $feedback_token
 * @property Carbon|null $feedback_requested_at
 * @property int $feedback_request_failures
 */
final class Lead extends Model
{
    use HasFactory;

    public const string STATUS_LOST = 'verloren';

    public const string STATUS_WON = 'gewonnen';

    /** Aantal dagen na 'verloren' waarna we om feedback vragen. */
    public const int FEEDBACK_DELAY_DAYS = 14;

    /** Aantal dagen na de feedbackmail dat de persoonlijke link geldig blijft. */
    public const int FEEDBACK_LINK_VALID_DAYS = 90;

    /** Na zoveel mislukte verzendpogingen stopt de command met het mailen van deze lead. */
    public const int MAX_FEEDBACK_REQUEST_FAILURES = 3;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        // Houdt bij wanneer een lead verloren of gewonnen is, ongeacht waar de status wordt
        // gewijzigd (detailpagina, tabel of code).
        self::saving(function (Lead $lead): void {
            if (! $lead->isDirty('status')) {
                return;
            }

            $lead->lost_at = $lead->status === self::STATUS_LOST ? now() : null;
            $lead->won_at = $lead->status === self::STATUS_WON ? now() : null;
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
            ->whereNull('feedback_requested_at')
            ->where('feedback_request_failures', '<', self::MAX_FEEDBACK_REQUEST_FAILURES);
    }

    /**
     * Unieke, niet te raden token voor de persoonlijke feedbacklink.
     */
    public function ensureFeedbackToken(): string
    {
        if ($this->feedback_token === null) {
            $this->forceFill(['feedback_token' => Str::random(48)])->save();
        }

        return $this->feedback_token;
    }

    /**
     * Persoonlijke feedbacklink. Zonder bijwerkingen: de token moet al bestaan (zie ensureFeedbackToken()).
     *
     * @throws LogicException
     */
    public function feedbackUrl(): string
    {
        if ($this->feedback_token === null) {
            throw new LogicException("Lead #{$this->id} heeft nog geen feedbacktoken.");
        }

        return route('feedback.show', ['token' => $this->feedback_token]);
    }

    /**
     * De feedbacklink werkt alleen zolang de lead verloren is en de mail niet ouder is dan 90 dagen.
     */
    public function hasActiveFeedbackLink(): bool
    {
        return $this->status === self::STATUS_LOST
            && $this->feedback_requested_at !== null
            && $this->feedback_requested_at->greaterThanOrEqualTo(now()->subDays(self::FEEDBACK_LINK_VALID_DAYS));
    }

    /**
     * Of deze lead het maximum aantal feedbackberichten al heeft ingestuurd.
     */
    public function hasReachedFeedbackLimit(): bool
    {
        return $this->feedback()->count() >= LeadFeedback::MAX_PER_LEAD;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lost_at' => 'datetime',
            'won_at' => 'datetime',
            'feedback_requested_at' => 'datetime',
            'feedback_request_failures' => 'integer',
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
