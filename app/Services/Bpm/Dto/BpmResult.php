<?php

declare(strict_types=1);

namespace App\Services\Bpm\Dto;

final readonly class BpmResult
{
    /**
     * @param  list<string>  $notes
     */
    public function __construct(
        public bool $isEligible,
        public ?string $ineligibleReason,
        public float $brutoBpm,
        public float $afschrijvingPercentage,
        public int $ageMonths,
        public float $restBpm,
        public string $method,
        public array $notes,
        public ?float $calculatedRestBpm = null,
    ) {}

    public static function eligible(
        float $brutoBpm,
        float $afschrijvingPercentage,
        int $ageMonths,
        float $restBpm,
        array $notes = [],
        string $method = 'forfaitair',
    ): self {
        return new self(
            isEligible: true,
            ineligibleReason: null,
            brutoBpm: $brutoBpm,
            afschrijvingPercentage: $afschrijvingPercentage,
            ageMonths: $ageMonths,
            restBpm: $restBpm,
            method: $method,
            notes: $notes,
        );
    }

    /**
     * The calculation itself is shown, but nothing is paid out: the refund is
     * below the Belastingdienst minimum (or there was no BPM at all).
     *
     * @param  list<string>  $notes
     */
    public static function belowMinimum(
        string $reason,
        float $brutoBpm,
        float $afschrijvingPercentage,
        int $ageMonths,
        float $calculatedRestBpm,
        array $notes = [],
    ): self {
        return new self(
            isEligible: false,
            ineligibleReason: $reason,
            brutoBpm: $brutoBpm,
            afschrijvingPercentage: $afschrijvingPercentage,
            ageMonths: $ageMonths,
            restBpm: 0.0,
            method: 'forfaitair',
            notes: $notes,
            calculatedRestBpm: $calculatedRestBpm,
        );
    }

    public static function notEligible(string $reason): self
    {
        return new self(
            isEligible: false,
            ineligibleReason: $reason,
            brutoBpm: 0.0,
            afschrijvingPercentage: 0.0,
            ageMonths: 0,
            restBpm: 0.0,
            method: 'forfaitair',
            notes: [],
        );
    }
}
