<?php

declare(strict_types=1);

namespace App\Services\SpainImport\Dto;

final readonly class SpainImportResult
{
    /**
     * @param  list<FixedCostItem>  $fixedCosts
     * @param  list<string>  $notes
     */
    public function __construct(
        public float $iedmtEur,
        public float $iedmtRatePct,
        public bool $iedmtExempt,
        public ?string $iedmtExemptReason,
        public float $estimatedMarketValueEur,
        public array $fixedCosts,
        public float $fixedCostsTotalEur,
        public float $totalEur,
        public string $autonomia,
        public array $notes,
    ) {}
}
