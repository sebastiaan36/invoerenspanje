<?php

declare(strict_types=1);

namespace App\Services\Hacienda\Dto;

/**
 * The Hacienda new value ("precio medio de venta") found for a vehicle.
 */
final readonly class HaciendaMatch
{
    public const string CONFIDENCE_HIGH = 'hoog';

    public const string CONFIDENCE_MEDIUM = 'gemiddeld';

    public const string CONFIDENCE_LOW = 'laag';

    public function __construct(
        public int $newValueEur,
        public string $brand,
        public string $model,
        public int $tableYear,
        public int $candidateCount,
        public string $confidence,
    ) {}

    public function description(): string
    {
        return trim("{$this->brand} {$this->model}");
    }
}
