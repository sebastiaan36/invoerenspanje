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
