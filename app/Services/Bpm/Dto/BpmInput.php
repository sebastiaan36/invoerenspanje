<?php

declare(strict_types=1);

namespace App\Services\Bpm\Dto;

use App\Services\Rdw\Dto\VehicleLookupResult;
use Carbon\CarbonImmutable;

final readonly class BpmInput
{
    /**
     * RDW fuel types without tailpipe CO2: battery-electric and hydrogen
     * (fuel cell). The BPM treats both as zero-emission cars.
     *
     * @var list<string>
     */
    public const array ZERO_EMISSION_FUELS = ['elektriciteit', 'elektrisch', 'waterstof'];

    public function __construct(
        public CarbonImmutable $datumEersteToelating,
        public int $co2,
        public string $brandstof,
        public bool $isPlugin = false,
        public ?string $voertuigsoort = null,
        public ?int $historischBrutoBpm = null,
        public ?CarbonImmutable $datumEersteTenaamstellingNl = null,
    ) {}

    /**
     * A used import: first registered abroad, more than a month before its
     * first Dutch registration. The BPM actually paid is then usually lower
     * than the bruto BPM, which the RDW open data does not expose.
     */
    public function isImported(): bool
    {
        return $this->datumEersteTenaamstellingNl !== null
            && $this->datumEersteTenaamstellingNl->gt($this->datumEersteToelating->addMonthNoOverflow());
    }

    /**
     * Build a BpmInput from a successful RDW lookup.
     * Returns null when required fields are missing (the UI then shows
     * "niet automatisch te berekenen" instead of an amount).
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
            ? ($fuel->co2WltpGewogen ?? $fuel->co2UitstootGewogen ?? $fuel->co2WltpGecombineerd ?? $fuel->co2UitstootGecombineerd)
            : ($fuel->co2WltpGecombineerd ?? $fuel->co2UitstootGecombineerd ?? $fuel->co2WltpGewogen ?? $fuel->co2UitstootGewogen);

        // Without CO2 and without the RDW's own bruto BPM a combustion car would
        // be taxed on the fixed base only — a far too low amount presented as an
        // answer. Electric and hydrogen cars pay a fixed amount, so they don't need CO2.
        if ($co2 === null && $vehicle->brutoBpm === null && ! self::isZeroEmissionFuel($fuel->brandstofOmschrijving)) {
            return null;
        }

        return new self(
            datumEersteToelating: $vehicle->datumEersteToelating,
            co2: $co2 ?? 0,
            brandstof: $fuel->brandstofOmschrijving,
            isPlugin: $result->isPluginHybrid,
            voertuigsoort: $vehicle->voertuigsoort,
            historischBrutoBpm: $vehicle->brutoBpm,
            datumEersteTenaamstellingNl: $vehicle->datumEersteTenaamstellingNl,
        );
    }

    public static function isZeroEmissionFuel(string $brandstof): bool
    {
        return in_array(strtolower($brandstof), self::ZERO_EMISSION_FUELS, true);
    }
}
