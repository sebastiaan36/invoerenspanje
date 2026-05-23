<?php

declare(strict_types=1);

namespace App\Services\SpainImport\Dto;

use App\Services\Rdw\Dto\VehicleLookupResult;
use Carbon\CarbonImmutable;

final readonly class SpainImportInput
{
    public function __construct(
        public CarbonImmutable $datumEersteToelating,
        public int $co2,
        public ?int $catalogusprijsEur,
        public ?string $voertuigsoort,
        public bool $residencyChange = false,
        public string $autonomia = 'default',
    ) {}

    public static function fromLookup(
        VehicleLookupResult $result,
        bool $residencyChange = false,
        string $autonomia = 'default',
    ): ?self {
        $vehicle = $result->vehicle;
        $fuel = $result->fuel;

        if ($vehicle === null || $vehicle->datumEersteToelating === null) {
            return null;
        }

        // WLTP heeft prioriteit boven NEDC (co2_uitstoot_*).
        $co2 = $fuel?->co2WltpGecombineerd ?? $fuel?->co2WltpGewogen ?? $fuel?->co2UitstootGecombineerd ?? $fuel?->co2UitstootGewogen ?? 0;

        return new self(
            datumEersteToelating: $vehicle->datumEersteToelating,
            co2: $co2,
            catalogusprijsEur: $vehicle->catalogusprijs,
            voertuigsoort: $vehicle->voertuigsoort,
            residencyChange: $residencyChange,
            autonomia: $autonomia,
        );
    }
}
