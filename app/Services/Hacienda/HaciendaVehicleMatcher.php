<?php

declare(strict_types=1);

namespace App\Services\Hacienda;

use App\Models\HaciendaVehiclePrice;
use App\Services\Hacienda\Dto\HaciendaMatch;
use App\Services\Rdw\Dto\VehicleLookupResult;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

/**
 * Finds the Hacienda new value for an RDW vehicle in the Orden HAC table.
 *
 * Matching uses brand, model family (first word of the RDW handelsbenaming),
 * the commercial period, fuel type, cylinder capacity and power. Hacienda
 * lists many trim levels per engine; the median of the closest rows is used,
 * just like a gestor would pick "the nearest version".
 *
 * Only the rows of the brand whose model name can contain the model family
 * are loaded, and a match is cached per vehicle and imported table version.
 */
final class HaciendaVehicleMatcher
{
    /**
     * RDW merk (normalized) → Hacienda marca (normalized).
     *
     * @var array<string, string>
     */
    private const array BRAND_ALIASES = [
        'MERCEDESBENZ' => 'MERCEDES',
        'MERCEDESAMG' => 'MERCEDES',
        'SSANGYONG' => 'SSANYONG',
        'VW' => 'VOLKSWAGEN',
        'BMWI' => 'BMW',
        'BMWALPINA' => 'BMW',
        'LYNKCO' => 'LYNKCO',
        'DSAUTOMOBILES' => 'DS',
        'TESLAMOTORS' => 'TESLA',
    ];

    private const int TIGHT_CC_TOLERANCE = 20;

    private const int TIGHT_KW_TOLERANCE = 3;

    private const int LOOSE_CC_TOLERANCE = 60;

    /**
     * Characters that may appear accented in a Hacienda model name; in the SQL
     * pre-filter they match any single character.
     */
    private const string ACCENTABLE_CHARACTERS = 'AEIOUNC';

    private ?bool $hasTable = null;

    /**
     * @param  CacheRepository|null  $cache  caches match results; without it every call queries the table
     * @param  int  $cacheTtlSeconds  lifetime of a cached match (the key also changes with every re-import)
     */
    public function __construct(
        private readonly int $tableYear,
        private readonly ?CacheRepository $cache = null,
        private readonly ?LoggerInterface $logger = null,
        private readonly int $cacheTtlSeconds = 86_400,
    ) {}

    /**
     * Whether the configured table year has imported rows. When it has none,
     * a warning is logged (at most once a day) instead of silently falling
     * back to the catalogue price for every vehicle.
     */
    public function hasTable(): bool
    {
        if ($this->hasTable !== null) {
            return $this->hasTable;
        }

        $this->hasTable = HaciendaVehiclePrice::query()->where('table_year', $this->tableYear)->exists();

        if (! $this->hasTable && $this->shouldWarnAboutMissingTable()) {
            $this->logger?->warning('Hacienda price table has no rows for the configured table year; falling back to the catalogue price.', [
                'table_year' => $this->tableYear,
            ]);
        }

        return $this->hasTable;
    }

    public function match(VehicleLookupResult $rdw): ?HaciendaMatch
    {
        $cacheKey = $this->matchCacheKey($rdw);

        if ($cacheKey === null) {
            return $this->findMatch($rdw);
        }

        $cached = $this->cache?->get($cacheKey);

        if (is_array($cached) && array_key_exists('match', $cached)) {
            return $cached['match'] === null ? null : new HaciendaMatch(...$cached['match']);
        }

        $match = $this->findMatch($rdw);

        $this->cache?->put($cacheKey, ['match' => $match === null ? null : [
            'newValueEur' => $match->newValueEur,
            'brand' => $match->brand,
            'model' => $match->model,
            'tableYear' => $match->tableYear,
            'candidateCount' => $match->candidateCount,
            'confidence' => $match->confidence,
        ]], $this->cacheTtlSeconds);

        return $match;
    }

    /**
     * Cache key per matching input and imported table version (the import
     * checksum). Null when there is no cache or the table was not loaded via
     * the importer, so a changed table can never serve a stale match.
     */
    private function matchCacheKey(VehicleLookupResult $rdw): ?string
    {
        if ($this->cache === null) {
            return null;
        }

        $checksum = $this->cache->get(HaciendaPriceImporter::checksumCacheKey($this->tableYear));

        if (! is_string($checksum) || $checksum === '') {
            return null;
        }

        $inputs = [
            $rdw->vehicle?->merk,
            $rdw->vehicle?->handelsbenaming,
            $rdw->vehicle?->datumEersteToelating?->year,
            $rdw->vehicle?->cilinderinhoud,
            $rdw->fuel?->powerKw(),
            $rdw->fuel?->brandstofOmschrijving,
            $rdw->isPluginHybrid,
        ];

        return "hacienda:match:{$this->tableYear}:{$checksum}:".sha1((string) json_encode($inputs));
    }

    private function shouldWarnAboutMissingTable(): bool
    {
        return $this->cache === null
            || $this->cache->add("hacienda:missing-table-warned:{$this->tableYear}", true, 86_400);
    }

    private function findMatch(VehicleLookupResult $rdw): ?HaciendaMatch
    {
        $vehicle = $rdw->vehicle;

        if ($vehicle === null || $vehicle->merk === null || $vehicle->datumEersteToelating === null) {
            return null;
        }

        $brandKey = $this->brandKey($vehicle->merk);
        $year = $vehicle->datumEersteToelating->year;
        $cc = $vehicle->cilinderinhoud > 0 ? $vehicle->cilinderinhoud : null;
        $kw = $rdw->fuel?->powerKw();
        $brandWords = $this->brandWords($vehicle->merk, $brandKey);
        $family = $this->modelFamily((string) $vehicle->handelsbenaming, $brandKey, $brandWords);
        $nameTokens = $this->nameTokens((string) $vehicle->handelsbenaming, $brandWords, $family);

        // Without a model family nothing can match (see step 2 below).
        if ($family === '') {
            return null;
        }

        $brandRows = HaciendaVehiclePrice::query()
            ->select(['id', 'brand', 'model', 'start_year', 'end_year', 'cc', 'fuel', 'kw', 'price_eur'])
            ->where('table_year', $this->tableYear)
            ->where('brand_key', $brandKey)
            ->whereRaw('UPPER(model) LIKE ?', [$this->familyLikePattern($family)])
            ->get();

        if ($brandRows->isEmpty()) {
            return null;
        }

        $familyRows = $brandRows->filter(fn (HaciendaVehiclePrice $row) => $this->isSameFamily($row->model, $family, $brandKey, $brandWords));

        // Model code later in the Hacienda name (BMW "220I" → "Serie 2 Active Tourer 220i").
        if ($familyRows->isEmpty()) {
            $familyRows = $brandRows->filter(fn (HaciendaVehiclePrice $row) => $this->containsModelCode($row->model, $family));
        }
        $fuelGroups = $this->fuelCodeGroups($rdw);

        // 1. The model is in the table: never switch to another model of the brand.
        //    Prefer the same fuel; if Hacienda only lists other fuels (e.g. a C-HR
        //    only as hybrid), take the closest engine of that model anyway.
        if ($familyRows->isNotEmpty()) {
            foreach ([...$fuelGroups, null] as $fuels) {
                $rows = $fuels === null ? $familyRows : $familyRows->whereIn('fuel', $fuels);
                $match = $this->familyMatch($rows, $nameTokens, $year, $cc, $kw);

                if ($match !== null) {
                    return $match;
                }
            }
        }

        // 2. The model is not in the table: no Hacienda value. Never fall back to
        //    another model with the same engine (e.g. a Kia K4 is not a Stonic);
        //    the calculator then uses the catalogue price and says so.
        return null;
    }

    /**
     * Best rows of one model: same engine (high), same engine size (medium),
     * otherwise the closest engine and commercial period of that model (low).
     *
     * @param  Collection<int, HaciendaVehiclePrice>  $rows
     * @param  list<string>  $nameTokens
     */
    private function familyMatch(Collection $rows, array $nameTokens, int $year, ?int $cc, ?int $kw): ?HaciendaMatch
    {
        if ($rows->isEmpty()) {
            return null;
        }

        $inPeriod = $this->inPeriod($rows, $year);

        if ($inPeriod->isNotEmpty()) {
            $tight = $this->tight($inPeriod, $cc, $kw);
            if ($tight->isNotEmpty()) {
                return $this->result($this->closestName($tight, $nameTokens), HaciendaMatch::CONFIDENCE_HIGH);
            }

            $sameEngineSize = $cc === null
                ? $inPeriod
                : $inPeriod->filter(fn (HaciendaVehiclePrice $row) => $row->cc === null || abs($row->cc - $cc) <= self::LOOSE_CC_TOLERANCE);

            if ($sameEngineSize->isNotEmpty()) {
                return $this->result($this->nearestPower($this->closestName($sameEngineSize, $nameTokens), $kw), HaciendaMatch::CONFIDENCE_MEDIUM);
            }
        }

        $closest = $this->closestEngine($this->closestPeriod($inPeriod->isNotEmpty() ? $inPeriod : $rows, $year), $cc, $kw);

        return $this->result($this->closestName($closest, $nameTokens), HaciendaMatch::CONFIDENCE_LOW);
    }

    /**
     * Rows whose commercial period is nearest to the year of first registration.
     *
     * @param  Collection<int, HaciendaVehiclePrice>  $rows
     * @return Collection<int, HaciendaVehiclePrice>
     */
    private function closestPeriod(Collection $rows, int $year): Collection
    {
        $distance = fn (HaciendaVehiclePrice $row): int => max(0, $row->start_year - $year, $year - ($row->end_year ?? $year));
        $smallest = $rows->min($distance);

        return $rows->filter(fn (HaciendaVehiclePrice $row) => $distance($row) === $smallest);
    }

    /**
     * Rows with the cylinder capacity closest to the vehicle, then the closest power.
     *
     * @param  Collection<int, HaciendaVehiclePrice>  $rows
     * @return Collection<int, HaciendaVehiclePrice>
     */
    private function closestEngine(Collection $rows, ?int $cc, ?int $kw): Collection
    {
        if ($cc !== null && $rows->contains(fn (HaciendaVehiclePrice $row) => $row->cc !== null)) {
            $withCc = $rows->filter(fn (HaciendaVehiclePrice $row) => $row->cc !== null);
            $smallestGap = $withCc->min(fn (HaciendaVehiclePrice $row) => abs($row->cc - $cc));
            $rows = $withCc->filter(fn (HaciendaVehiclePrice $row) => abs($row->cc - $cc) === $smallestGap);
        }

        return $this->nearestPower($rows, $kw);
    }

    /**
     * A LIKE pattern every model name of the family satisfies: both family
     * checks (isSameFamily, containsModelCode) compare glued alphanumeric words,
     * so the family's characters always appear in order in the model name.
     * Characters that may be accented in the table match any single character.
     * This only narrows the rows; the exact checks still run in PHP.
     */
    private function familyLikePattern(string $family): string
    {
        $characters = array_map(
            fn (string $character): string => str_contains(self::ACCENTABLE_CHARACTERS, $character) ? '_' : $character,
            str_split($family),
        );

        return '%'.implode('%', $characters).'%';
    }

    private function brandKey(string $merk): string
    {
        $key = HaciendaVehiclePrice::normalizeKey($merk);

        return self::BRAND_ALIASES[$key] ?? $key;
    }

    /**
     * Hacienda fuel codes that fit the RDW fuel, tried in order. Hacienda only
     * started listing plug-in hybrids as "PHEV" recently; older ones are listed
     * under their combustion fuel. Mild/full hybrids may be listed with or without "yE".
     *
     * @return list<list<string>>
     */
    private function fuelCodeGroups(VehicleLookupResult $rdw): array
    {
        $combustion = match (strtolower((string) $rdw->fuel?->brandstofOmschrijving)) {
            'diesel' => ['DyE', 'D'],
            'elektriciteit', 'elektrisch' => ['Elc'],
            'lpg' => ['S', 'SyE', 'G'],
            'waterstof' => ['H'],
            'cng', 'alcohol' => ['M', 'G'],
            default => ['GyE', 'G', 'M'],
        };

        return $rdw->isPluginHybrid ? [['PHEV'], $combustion] : [$combustion];
    }

    /**
     * Distinctive words of the RDW name besides the brand and model family,
     * e.g. ["XDRIVE20I"] for "X3 XDRIVE20I" or ["AVANT"] for "A4 AVANT".
     *
     * @param  list<string>  $brandWords
     * @return list<string>
     */
    private function nameTokens(string $handelsbenaming, array $brandWords, string $family): array
    {
        return array_values(array_unique(array_filter(
            $this->words($handelsbenaming),
            fn (string $word) => strlen($word) >= 2 && $word !== $family && ! in_array($word, $brandWords, true),
        )));
    }

    /**
     * Keep the rows sharing the most distinctive words with the RDW name.
     *
     * @param  Collection<int, HaciendaVehiclePrice>  $rows
     * @param  list<string>  $nameTokens
     * @return Collection<int, HaciendaVehiclePrice>
     */
    private function closestName(Collection $rows, array $nameTokens): Collection
    {
        if ($nameTokens === []) {
            return $rows;
        }

        $scores = $rows->mapWithKeys(function (HaciendaVehiclePrice $row, int $key) use ($nameTokens): array {
            $words = preg_split('/[^A-Z0-9+]+/', strtoupper(HaciendaVehiclePrice::stripAccents($row->model)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            return [$key => count(array_intersect($nameTokens, $words))];
        });

        $best = $scores->max();

        return $best > 0 ? $rows->filter(fn (HaciendaVehiclePrice $row, int $key) => $scores[$key] === $best) : $rows;
    }

    /**
     * First word of the RDW handelsbenaming, without a leading brand name and
     * a trailing "-KLASSE" (Mercedes "GLB-KLASSE" → "GLB").
     */
    /**
     * @param  list<string>  $brandWords
     */
    private function modelFamily(string $handelsbenaming, string $brandKey, array $brandWords): string
    {
        $words = $this->withoutLeadingBrandWords(
            preg_split('/\s+/', trim(strtoupper(HaciendaVehiclePrice::stripAccents($handelsbenaming))), -1, PREG_SPLIT_NO_EMPTY) ?: [],
            $brandKey,
            $brandWords,
        );

        $family = (string) preg_replace('/-?KLASSE$/', '', $words[0] ?? '');

        return HaciendaVehiclePrice::normalizeKey($family);
    }

    /**
     * RDW and Hacienda both sometimes repeat (part of) the brand before the
     * model: "TOYOTA C-HR", "ALFA GIULIETTA", "ALFA 159", "Mercedes-AMG E 53".
     *
     * @param  list<string>  $words
     * @param  list<string>  $brandWords
     * @return list<string>
     */
    private function withoutLeadingBrandWords(array $words, string $brandKey, array $brandWords): array
    {
        while (count($words) > 1) {
            $first = HaciendaVehiclePrice::normalizeKey($words[0]);

            if ($first !== $brandKey && ! in_array($first, $brandWords, true)) {
                break;
            }

            array_shift($words);
        }

        return array_values($words);
    }

    /**
     * Normalized words of the RDW merk and the Hacienda marca ("ALFA ROMEO" → ALFA, ROMEO).
     *
     * @return list<string>
     */
    private function brandWords(string $merk, string $brandKey): array
    {
        $words = preg_split('/[^A-Z0-9]+/', strtoupper(HaciendaVehiclePrice::stripAccents($merk)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique([...$words, $brandKey]));
    }

    /**
     * Hacienda writes "V 50", "T-Roc" or "Land Cruiser" where the RDW has
     * "V50", "T-ROC" and "LANDCRUISER": compare the first words glued together.
     * "A3" must not match "A35", but "320D" may match "320dA".
     *
     * @param  list<string>  $brandWords
     */
    private function isSameFamily(string $hacienda, string $family, string $brandKey, array $brandWords): bool
    {
        $words = $this->withoutLeadingBrandWords($this->words($hacienda), $brandKey, $brandWords);
        $glued = '';

        foreach (array_slice($words, 0, 3) as $index => $word) {
            $glued .= $word;

            if ($glued === $family) {
                return true;
            }

            if ($index === 0 && preg_match('/\d/', $family) === 1
                && str_starts_with($glued, $family) && ctype_alpha($glued[strlen($family)] ?? '')) {
                return true;
            }

            if (strlen($glued) >= strlen($family)) {
                return false;
            }
        }

        return false;
    }

    /**
     * A model code that Hacienda puts later in the name, e.g. BMW "220I" in
     * "Serie 2 Active Tourer 220i". Only for codes with a digit, so ordinary
     * words like "SPORTBACK" never match.
     */
    private function containsModelCode(string $hacienda, string $family): bool
    {
        if (strlen($family) < 3 || preg_match('/\d/', $family) !== 1) {
            return false;
        }

        $words = $this->words($hacienda);

        foreach ($words as $index => $word) {
            if ($word === $family || $word.($words[$index + 1] ?? '') === $family) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function words(string $value): array
    {
        return preg_split('/[^A-Z0-9]+/', strtoupper(HaciendaVehiclePrice::stripAccents($value)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * Rows whose commercial period covers the year of first registration,
     * widened by a year when nothing fits exactly.
     *
     * @param  Collection<int, HaciendaVehiclePrice>  $rows
     * @return Collection<int, HaciendaVehiclePrice>
     */
    private function inPeriod(Collection $rows, int $year, int $tolerance = 1): Collection
    {
        foreach (array_unique([0, $tolerance]) as $margin) {
            $inPeriod = $rows->filter(fn (HaciendaVehiclePrice $row) => $row->start_year - $margin <= $year
                && ($row->end_year === null || $year <= $row->end_year + $margin));

            if ($inPeriod->isNotEmpty()) {
                return $inPeriod;
            }
        }

        return collect();
    }

    /**
     * @param  Collection<int, HaciendaVehiclePrice>  $rows
     * @return Collection<int, HaciendaVehiclePrice>
     */
    private function tight(Collection $rows, ?int $cc, ?int $kw): Collection
    {
        if ($kw === null) {
            return collect();
        }

        return $rows->filter(fn (HaciendaVehiclePrice $row) => abs($row->kw - $kw) <= self::TIGHT_KW_TOLERANCE
            && ($cc === null || $row->cc === null || abs($row->cc - $cc) <= self::TIGHT_CC_TOLERANCE));
    }

    /**
     * @param  Collection<int, HaciendaVehiclePrice>  $rows
     * @return Collection<int, HaciendaVehiclePrice>
     */
    private function nearestPower(Collection $rows, ?int $kw): Collection
    {
        if ($kw === null) {
            return $rows;
        }

        $smallestGap = $rows->min(fn (HaciendaVehiclePrice $row) => abs($row->kw - $kw));

        return $rows->filter(fn (HaciendaVehiclePrice $row) => abs($row->kw - $kw) === $smallestGap);
    }

    /**
     * @param  Collection<int, HaciendaVehiclePrice>  $rows
     */
    private function result(Collection $rows, string $confidence): HaciendaMatch
    {
        $sorted = $rows->sortBy('price_eur')->values();
        $count = $sorted->count();
        $middle = intdiv($count, 2);

        $median = $count % 2 === 1
            ? $sorted[$middle]->price_eur
            : (int) round(($sorted[$middle - 1]->price_eur + $sorted[$middle]->price_eur) / 2);

        /** @var HaciendaVehiclePrice $representative */
        $representative = $sorted->sortBy(fn (HaciendaVehiclePrice $row) => abs($row->price_eur - $median))->first();

        return new HaciendaMatch(
            newValueEur: $median,
            brand: $representative->brand,
            model: $representative->model,
            tableYear: $this->tableYear,
            candidateCount: $count,
            confidence: $confidence,
        );
    }
}
