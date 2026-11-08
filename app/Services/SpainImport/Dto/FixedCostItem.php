<?php

declare(strict_types=1);

namespace App\Services\SpainImport\Dto;

final readonly class FixedCostItem
{
    /**
     * @param  bool  $optional  listed for information, but excluded from every total
     */
    public function __construct(
        public string $key,
        public string $label,
        public float $amountEur,
        public bool $optional = false,
    ) {}
}
