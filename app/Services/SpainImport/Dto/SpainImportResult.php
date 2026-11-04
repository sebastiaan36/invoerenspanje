<?php

declare(strict_types=1);

namespace App\Services\SpainImport\Dto;

final readonly class SpainImportResult
{
    public const string VALUE_SOURCE_HACIENDA = 'hacienda';

    public const string VALUE_SOURCE_CATALOGUE_PRICE = 'catalogusprijs';

    public const string VALUE_SOURCE_NONE = 'onbekend';

    /**
     * @param  float  $estimatedMarketValueEur  new value × Anexo IV percentage
     * @param  float  $taxableValueEur  market value after the art. 5 correction; the IEDMT base
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
        public string $valueSource = self::VALUE_SOURCE_NONE,
        public float $newValueEur = 0.0,
        public ?string $haciendaModel = null,
        public ?string $haciendaConfidence = null,
        public int $retentionPct = 100,
        public float $taxableValueEur = 0.0,
    ) {}
}
