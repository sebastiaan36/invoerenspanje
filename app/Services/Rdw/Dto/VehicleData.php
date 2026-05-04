<?php

declare(strict_types=1);

namespace App\Services\Rdw\Dto;

use Carbon\CarbonImmutable;

final readonly class VehicleData
{
    public function __construct(
        public string $kenteken,
        public ?string $voertuigsoort,
        public ?string $merk,
        public ?string $handelsbenaming,
        public ?string $inrichting,
        public ?string $eersteKleur,
        public ?int $aantalZitplaatsen,
        public ?CarbonImmutable $datumEersteToelating,
        public ?CarbonImmutable $datumEersteTenaamstellingNl,
        public ?CarbonImmutable $vervaldatumApk,
        public ?int $massaLedigVoertuig,
        public ?int $cilinderinhoud,
        public ?int $catalogusprijs,
        public ?bool $wamVerzekerd,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromRdwRow(array $row): self
    {
        return new self(
            kenteken: (string) ($row['kenteken'] ?? ''),
            voertuigsoort: self::str($row['voertuigsoort'] ?? null),
            merk: self::str($row['merk'] ?? null),
            handelsbenaming: self::str($row['handelsbenaming'] ?? null),
            inrichting: self::str($row['inrichting'] ?? null),
            eersteKleur: self::str($row['eerste_kleur'] ?? null),
            aantalZitplaatsen: self::int($row['aantal_zitplaatsen'] ?? null),
            datumEersteToelating: self::date($row['datum_eerste_toelating'] ?? null),
            datumEersteTenaamstellingNl: self::date($row['datum_eerste_tenaamstelling_in_nederland'] ?? null),
            vervaldatumApk: self::date($row['vervaldatum_apk'] ?? null),
            massaLedigVoertuig: self::int($row['massa_ledig_voertuig'] ?? null),
            cilinderinhoud: self::int($row['cilinderinhoud'] ?? null),
            catalogusprijs: self::int($row['catalogusprijs'] ?? null),
            wamVerzekerd: self::bool($row['wam_verzekerd'] ?? null),
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

    private static function bool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return in_array(strtolower((string) $value), ['ja', 'true', '1', 'yes'], true);
    }

    private static function date(mixed $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;

        // RDW serves dates as YYYYMMDD (e.g. "20190401").
        if (preg_match('/^\d{8}$/', $value) === 1) {
            return CarbonImmutable::createFromFormat('Ymd', $value)?->startOfDay();
        }

        return CarbonImmutable::parse($value);
    }
}
