<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LeadFeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Feedback van een verloren lead op de vraag waarom hij niet voor ons koos.
 *
 * Alleen-toevoegen: een eenmaal opgeslagen bericht kan niet worden gewijzigd of
 * verwijderd, ook niet via de backend.
 *
 * @property int $id
 * @property int|null $lead_id
 * @property string $lead_name
 * @property string $lead_email
 * @property string|null $kenteken
 * @property string $message
 * @property Carbon $created_at
 */
final class LeadFeedback extends Model
{
    /** @use HasFactory<LeadFeedbackFactory> */
    use HasFactory;

    protected $table = 'lead_feedback';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        self::updating(function (): void {
            throw new LogicException('Feedback kan niet worden gewijzigd.');
        });

        self::deleting(function (): void {
            throw new LogicException('Feedback kan niet worden verwijderd.');
        });
    }

    /**
     * Legt de feedback vast met een momentopname van de leadgegevens.
     */
    public static function recordFor(Lead $lead, string $message): self
    {
        return self::query()->create([
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'lead_email' => $lead->email,
            'kenteken' => $lead->kenteken,
            'message' => $message,
        ]);
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
