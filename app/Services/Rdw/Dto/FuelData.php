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
        public ?string $emissiecodeOmschrijving,
    ) {}

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
            emissiecodeOmschrijving: self::str($row['emissiecode_omschrijving'] ?? null),
        );
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
