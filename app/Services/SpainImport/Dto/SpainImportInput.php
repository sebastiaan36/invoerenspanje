<?php

declare(strict_types=1);

namespace App\Services\SpainImport\Dto;

use App\Services\Hacienda\Dto\HaciendaMatch;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Carbon\CarbonImmutable;

final readonly class SpainImportInput
{
    /**
     * @param  int|null  $co2  WLTP (or NEDC) g/km; null when the RDW has no value
     * @param  bool  $isZeroEmission  fully electric or hydrogen: always 0% IEDMT
     */
    public function __construct(
        public CarbonImmutable $datumEersteToelating,
        public ?int $co2,
        public ?int $catalogusprijsEur,
        public ?string $voertuigsoort,
        public bool $residencyChange = false,
        public string $autonomia = 'default',
        public ?HaciendaMatch $hacienda = null,
        public bool $isZeroEmission = false,
    ) {}

    public static function fromLookup(
        VehicleLookupResult $result,
        bool $residencyChange = false,
        string $autonomia = 'default',
        ?HaciendaMatch $hacienda = null,
    ): ?self {
        $vehicle = $result->vehicle;
        $fuel = $result->fuel;

        if ($vehicle === null || $vehicle->datumEersteToelating === null) {
            return null;
        }

        $isZeroEmission = ! $result->isPluginHybrid
            && in_array(strtolower((string) $fuel?->brandstofOmschrijving), ['elektriciteit', 'elektrisch', 'waterstof'], true);

        // WLTP heeft prioriteit boven NEDC (co2_uitstoot_*).
        $co2 = $fuel?->co2WltpGecombineerd ?? $fuel?->co2WltpGewogen ?? $fuel?->co2UitstootGecombineerd ?? $fuel?->co2UitstootGewogen;

        return new self(
            datumEersteToelating: $vehicle->datumEersteToelating,
            co2: $isZeroEmission ? 0 : $co2,
            catalogusprijsEur: $vehicle->catalogusprijs,
            voertuigsoort: $vehicle->voertuigsoort,
            residencyChange: $residencyChange,
            autonomia: $autonomia,
            hacienda: $hacienda,
            isZeroEmission: $isZeroEmission,
        );
    }

    public function withResidencyChange(bool $residencyChange): self
    {
        return new self(
            datumEersteToelating: $this->datumEersteToelating,
            co2: $this->co2,
            catalogusprijsEur: $this->catalogusprijsEur,
            voertuigsoort: $this->voertuigsoort,
            residencyChange: $residencyChange,
            autonomia: $this->autonomia,
            hacienda: $this->hacienda,
            isZeroEmission: $this->isZeroEmission,
        );
    }
}
