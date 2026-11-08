<?php

declare(strict_types=1);

namespace App\Services\SpainImport\Dto;

final readonly class SpainImportResult
{
    public const string VALUE_SOURCE_HACIENDA = 'hacienda';

    public const string VALUE_SOURCE_CATALOGUE_PRICE = 'catalogusprijs';

    public const string VALUE_SOURCE_NONE = 'onbekend';

    /**
     * @param  float|null  $iedmtEur  null when the IEDMT cannot be determined automatically (see $iedmtDeterminable)
     * @param  float|null  $iedmtRatePct  null when the CO2 (and so the bracket) is unknown
     * @param  float  $estimatedMarketValueEur  new value × Anexo IV percentage
     * @param  list<FixedCostItem>  $fixedCosts  including optional items, which are not counted in the totals
     * @param  float  $fixedCostsTotalEur  sum of the non-optional fixed costs
     * @param  float|null  $totalEur  IEDMT + fixed costs; null when the IEDMT is unknown
     * @param  list<string>  $notes
     * @param  float  $taxableValueEur  market value after the art. 5 correction; the IEDMT base
     */
    public function __construct(
        public ?float $iedmtEur,
        public ?float $iedmtRatePct,
        public bool $iedmtExempt,
        public ?string $iedmtExemptReason,
        public float $estimatedMarketValueEur,
        public array $fixedCosts,
        public float $fixedCostsTotalEur,
        public ?float $totalEur,
        public string $autonomia,
        public array $notes,
        public string $valueSource = self::VALUE_SOURCE_NONE,
        public float $newValueEur = 0.0,
        public ?string $haciendaModel = null,
        public ?string $haciendaConfidence = null,
        public int $retentionPct = 100,
        public float $taxableValueEur = 0.0,
        public bool $iedmtDeterminable = true,
    ) {}
}
