<?php

declare(strict_types=1);

namespace App\Services\SpainImport\Dto;

final readonly class FixedCostItem
{
    public function __construct(
        public string $key,
        public string $label,
        public float $amountEur,
    ) {}
}
