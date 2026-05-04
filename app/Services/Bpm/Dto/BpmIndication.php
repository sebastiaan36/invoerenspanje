<?php

declare(strict_types=1);

namespace App\Services\Bpm\Dto;

final readonly class BpmIndication
{
    /**
     * @param  array<string, scalar|null>  $inputs  raw inputs used in the calculation, exposed for transparency
     * @param  list<string>  $notes  caveats or methodology notes (Dutch, end-user safe)
     */
    public function __construct(
        public int $estimatedRefundEur,
        public int $originalBpmEur,
        public float $depreciationFactor,
        public array $inputs,
        public array $notes,
    ) {}
}
