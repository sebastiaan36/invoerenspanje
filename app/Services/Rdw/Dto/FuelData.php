<?php

declare(strict_types=1);

namespace App\Services\Rdw\Dto;

final readonly class FuelData
{
    public function __construct(
        public string $kenteken,
        public ?string $brandstofOmschrijving,
        public ?int $co2UitstootGecombineerd,
        public ?int $co2UitstootGewogen,
        public ?int $co2WltpGecombineerd,
        public ?int $co2WltpGewogen,
        public ?string $emissiecodeOmschrijving,
        public ?int $nettoMaximumVermogenKw = null,
        public ?int $nettoMaxVermogenElektrischKw = null,
        public ?string $klasseHybrideElektrischVoertuig = null,
    ) {}

    /**
     * Engine power in kW: the combustion engine's net power, or the electric
     * motor's net power for a fully electric vehicle.
     */
    public function powerKw(): ?int
    {
        return $this->nettoMaximumVermogenKw ?? $this->nettoMaxVermogenElektrischKw;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromRdwRow(array $row): self
    {
        return new self(
            kenteken: (string) ($row['kenteken'] ?? ''),
            brandstofOmschrijving: self::str($row['brandstof_omschrijving'] ?? null),
            co2UitstootGecombineerd: self::int($row['co2_uitstoot_gecombineerd'] ?? null),
            co2UitstootGewogen: self::int($row['co2_uitstoot_gewogen'] ?? null),
            co2WltpGecombineerd: self::int($row['emissie_co2_gecombineerd_wltp'] ?? null),
            co2WltpGewogen: self::int($row['emissie_co2_gewogen_gecombineerd_wltp'] ?? null),
            emissiecodeOmschrijving: self::str($row['emissiecode_omschrijving'] ?? null),
            nettoMaximumVermogenKw: self::roundedInt($row['nettomaximumvermogen'] ?? null),
            nettoMaxVermogenElektrischKw: self::roundedInt($row['netto_max_vermogen_elektrisch'] ?? null),
            klasseHybrideElektrischVoertuig: self::str($row['klasse_hybride_elektrisch_voertuig'] ?? null),
        );
    }

    /**
     * RDW serves power as a decimal string ("110.00").
     */
    private static function roundedInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) round((float) $value);
    }

    private static function str(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private static function int(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
