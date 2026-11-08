<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LeadFeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LogicException;

/**
 * Feedback van een verloren lead op de vraag waarom hij niet voor ons koos.
 *
 * Alleen-toevoegen: een eenmaal opgeslagen bericht kan niet worden gewijzigd of
 * verwijderd, ook niet via de backend. De enige uitzondering is anonymizeByEmail()
 * voor een AVG-verwijderverzoek: die wist naam, e-mail en kenteken, nooit het bericht.
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

    /** Maximum aantal feedbackberichten per lead. */
    public const int MAX_PER_LEAD = 3;

    /** Velden die bij een verwijderverzoek (AVG) geanonimiseerd mogen worden; het bericht blijft staan. */
    private const array ANONYMIZABLE_FIELDS = ['lead_name', 'lead_email', 'kenteken', 'updated_at'];

    public const string ANONYMIZED_NAME = 'Geanonimiseerd';

    protected $table = 'lead_feedback';

    protected $guarded = ['id'];

    /**
     * Staat alleen tijdens anonymizeByEmail() aan, zodat de immutability-guard die ene wijziging doorlaat.
     */
    private static bool $isAnonymizing = false;

    protected static function booted(): void
    {
        self::updating(function (LeadFeedback $feedback): void {
            $changedFields = array_keys($feedback->getDirty());

            if (self::$isAnonymizing && array_diff($changedFields, self::ANONYMIZABLE_FIELDS) === []) {
                return;
            }

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
     * Anonimiseert naam, e-mail en kenteken op alle feedback van dit e-mailadres (AVG-verwijderverzoek).
     * Het bericht zelf blijft bewaard. Dit is de enige toegestane wijziging op feedback.
     *
     * @return int Aantal geanonimiseerde feedbackberichten.
     */
    public static function anonymizeByEmail(string $email): int
    {
        $normalizedEmail = Str::lower(trim($email));

        if ($normalizedEmail === '') {
            return 0;
        }

        $feedbackRows = self::query()->whereRaw('LOWER(lead_email) = ?', [$normalizedEmail])->get();

        self::$isAnonymizing = true;

        try {
            foreach ($feedbackRows as $feedback) {
                $feedback->forceFill([
                    'lead_name' => self::ANONYMIZED_NAME,
                    'lead_email' => "geanonimiseerd-{$feedback->id}@example.invalid",
                    'kenteken' => null,
                ])->save();
            }
        } finally {
            self::$isAnonymizing = false;
        }

        return $feedbackRows->count();
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
