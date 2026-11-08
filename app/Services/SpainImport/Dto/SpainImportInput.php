<?php

declare(strict_types=1);

namespace App\Services\SpainImport\Dto;

use App\Services\Hacienda\Dto\HaciendaMatch;
use App\Services\Rdw\Dto\FuelData;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Carbon\CarbonImmutable;

final readonly class SpainImportInput
{
    /**
     * @param  int|null  $co2  WLTP (or NEDC) g/km; the weighted value for a plug-in hybrid; null when the RDW has no value
     * @param  bool  $isZeroEmission  fully electric or hydrogen: always 0% IEDMT
     * @param  bool  $fuelDataMissing  the vehicle exists but the RDW has no usable fuel data, so the CO2 (and the IEDMT) is unknown
     * @param  bool  $haciendaTableAvailable  false when the configured Hacienda table year has no imported rows
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
        public bool $fuelDataMissing = false,
        public bool $haciendaTableAvailable = true,
    ) {}

    public static function fromLookup(
        VehicleLookupResult $result,
        bool $residencyChange = false,
        string $autonomia = 'default',
        ?HaciendaMatch $hacienda = null,
        bool $haciendaTableAvailable = true,
    ): ?self {
        $vehicle = $result->vehicle;
        $fuel = $result->fuel;

        if ($vehicle === null || $vehicle->datumEersteToelating === null) {
            return null;
        }

        $isZeroEmission = ! $result->isPluginHybrid
            && in_array(strtolower((string) $fuel?->brandstofOmschrijving), ['elektriciteit', 'elektrisch', 'waterstof'], true);

        // WLTP heeft prioriteit boven NEDC (co2_uitstoot_*). Een plug-in hybride
        // wordt, net als bij de BPM (BpmInput::fromLookup), over de gewogen CO2
        // beoordeeld en niet over de gecombineerde.
        $co2 = $result->isPluginHybrid
            ? ($fuel?->co2WltpGewogen ?? $fuel?->co2UitstootGewogen ?? $fuel?->co2WltpGecombineerd ?? $fuel?->co2UitstootGecombineerd)
            : ($fuel?->co2WltpGecombineerd ?? $fuel?->co2WltpGewogen ?? $fuel?->co2UitstootGecombineerd ?? $fuel?->co2UitstootGewogen);

        return new self(
            datumEersteToelating: $vehicle->datumEersteToelating,
            co2: $isZeroEmission ? 0 : $co2,
            catalogusprijsEur: $vehicle->catalogusprijs,
            voertuigsoort: $vehicle->voertuigsoort,
            residencyChange: $residencyChange,
            autonomia: $autonomia,
            hacienda: $hacienda,
            isZeroEmission: $isZeroEmission,
            fuelDataMissing: self::isFuelDataMissing($fuel),
            haciendaTableAvailable: $haciendaTableAvailable,
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
            fuelDataMissing: $this->fuelDataMissing,
            haciendaTableAvailable: $this->haciendaTableAvailable,
        );
    }

    /**
     * No fuel row at all, or a row without a fuel type and without any CO2 value.
     */
    private static function isFuelDataMissing(?FuelData $fuel): bool
    {
        if ($fuel === null) {
            return true;
        }

        return $fuel->brandstofOmschrijving === null
            && $fuel->co2WltpGecombineerd === null
            && $fuel->co2WltpGewogen === null
            && $fuel->co2UitstootGecombineerd === null
            && $fuel->co2UitstootGewogen === null;
    }
}
