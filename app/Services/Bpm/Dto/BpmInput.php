<?php

declare(strict_types=1);

namespace App\Services\Bpm\Dto;

use App\Services\Rdw\Dto\VehicleLookupResult;
use Carbon\CarbonImmutable;

final readonly class BpmInput
{
    public function __construct(
        public CarbonImmutable $datumEersteToelating,
        public int $co2,
        public string $brandstof,
        public bool $isPlugin = false,
        public ?string $voertuigsoort = null,
        public ?int $historischBrutoBpm = null,
    ) {}

    /**
     * Build a BpmInput from a successful RDW lookup.
     * Returns null when required fields are missing.
     */
    public static function fromLookup(VehicleLookupResult $result): ?self
    {
        $vehicle = $result->vehicle;
        $fuel = $result->fuel;

        if ($vehicle === null || $fuel === null) {
            return null;
        }

        if ($vehicle->datumEersteToelating === null || $fuel->brandstofOmschrijving === null) {
            return null;
        }

        // WLTP heeft prioriteit boven NEDC (veel auto's vanaf ~2020 hebben alleen
        // WLTP-waarden). Een PHEV wordt over de gewogen CO2 belast, niet over de
        // gecombineerde.
        $co2 = $result->isPluginHybrid
            ? ($fuel->co2WltpGewogen ?? $fuel->co2UitstootGewogen ?? $fuel->co2WltpGecombineerd ?? $fuel->co2UitstootGecombineerd ?? 0)
            : ($fuel->co2WltpGecombineerd ?? $fuel->co2UitstootGecombineerd ?? $fuel->co2WltpGewogen ?? $fuel->co2UitstootGewogen ?? 0);

        return new self(
            datumEersteToelating: $vehicle->datumEersteToelating,
            co2: $co2,
            brandstof: $fuel->brandstofOmschrijving,
            isPlugin: $result->isPluginHybrid,
            voertuigsoort: $vehicle->voertuigsoort,
            historischBrutoBpm: $vehicle->brutoBpm,
        );
    }
}
