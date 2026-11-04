<?php

declare(strict_types=1);

namespace App\Services\Hacienda;

use App\Models\HaciendaVehiclePrice;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Reads and writes the Hacienda price tables as CSV (committed to the repo,
 * one file per table year) and loads them into the database.
 */
final class HaciendaPriceImporter
{
    public const array CSV_HEADER = ['brand', 'model', 'start_year', 'end_year', 'cc', 'fuel', 'kw', 'cv', 'price_eur'];

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly string $directory,
    ) {}

    public function csvPath(int $tableYear): string
    {
        return $this->directory.DIRECTORY_SEPARATOR."precios_medios_{$tableYear}.csv";
    }

    /**
     * @param  list<array{brand: string, model: string, start_year: int, end_year: int|null, cc: int|null, fuel: string, kw: int, cv: int, price_eur: int}>  $rows
     */
    public function writeCsv(int $tableYear, array $rows): string
    {
        if (! is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }

        $path = $this->csvPath($tableYear);
        $handle = fopen($path, 'w') ?: throw new RuntimeException("Kan {$path} niet schrijven.");

        fputcsv($handle, self::CSV_HEADER, escape: '');
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn (string $column) => $row[$column], self::CSV_HEADER), escape: '');
        }
        fclose($handle);

        return $path;
    }

    /**
     * Loads every CSV whose contents changed since the last sync.
     *
     * @return array<int, array{status: 'imported'|'unchanged', rows: int}>
     */
    public function sync(bool $force = false): array
    {
        $result = [];

        foreach (glob($this->directory.DIRECTORY_SEPARATOR.'precios_medios_*.csv') ?: [] as $path) {
            if (preg_match('/precios_medios_(\d{4})\.csv$/', $path, $match) !== 1) {
                continue;
            }

            $tableYear = (int) $match[1];
            $checksum = (string) sha1_file($path);
            $cacheKey = "hacienda:prices:{$tableYear}:checksum";
            $existingRows = HaciendaVehiclePrice::query()->where('table_year', $tableYear)->count();

            if (! $force && $existingRows > 0 && $this->cache->get($cacheKey) === $checksum) {
                $result[$tableYear] = ['status' => 'unchanged', 'rows' => $existingRows];

                continue;
            }

            $result[$tableYear] = ['status' => 'imported', 'rows' => $this->import($tableYear, $path)];
            $this->cache->forever($cacheKey, $checksum);
        }

        return $result;
    }

    private function import(int $tableYear, string $path): int
    {
        $handle = fopen($path, 'r') ?: throw new RuntimeException("Kan {$path} niet lezen.");
        $header = fgetcsv($handle, escape: '');

        if ($header !== self::CSV_HEADER) {
            fclose($handle);

            throw new RuntimeException("Onverwachte kolommen in {$path}.");
        }

        $count = 0;

        DB::transaction(function () use ($handle, $tableYear, &$count): void {
            HaciendaVehiclePrice::query()->where('table_year', $tableYear)->delete();

            $batch = [];
            while (($values = fgetcsv($handle, escape: '')) !== false) {
                $row = array_combine(self::CSV_HEADER, $values);
                $batch[] = [
                    'table_year' => $tableYear,
                    'brand_key' => HaciendaVehiclePrice::normalizeKey($row['brand']),
                    'brand' => $row['brand'],
                    'model' => $row['model'],
                    'start_year' => (int) $row['start_year'],
                    'end_year' => $row['end_year'] === '' ? null : (int) $row['end_year'],
                    'cc' => $row['cc'] === '' ? null : (int) $row['cc'],
                    'fuel' => $row['fuel'],
                    'kw' => (int) $row['kw'],
                    'cv' => $row['cv'] === '' ? null : (int) $row['cv'],
                    'price_eur' => (int) $row['price_eur'],
                ];

                if (count($batch) === 500) {
                    HaciendaVehiclePrice::query()->insert($batch);
                    $count += count($batch);
                    $batch = [];
                }
            }

            if ($batch !== []) {
                HaciendaVehiclePrice::query()->insert($batch);
                $count += count($batch);
            }
        });

        fclose($handle);

        return $count;
    }
}
