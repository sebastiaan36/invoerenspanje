<?php

declare(strict_types=1);

namespace App\Services\Hacienda;

/**
 * Parses the passenger-car part of Anexo I of the yearly Orden HAC
 * "precios medios de venta", as produced by `pdftotext -layout`.
 *
 * Each table row reads (right to left): value, cv, cvf (optional),
 * kW, fuel, then cc + cylinders (optional), end year (optional) and start year.
 * Long model names wrap: the data row then has no name, with the first half
 * of the name on the line above and the second half on the line below.
 */
final class HaciendaPriceTableParser
{
    /**
     * A model name starts near the left margin; a data row whose name wrapped
     * starts with a wide indent, so its start year is never taken as the name.
     */
    private const string ROW_PATTERN = '/^(?:[ ]{0,12}(?<model>\S.*?))?\s{2,}(?<start>(?:19|20)\d{2})(?<rest>(?:\s+\d+)*)\s+(?<fuel>G|D|M|S|Elc|H|PHEV|GyE|DyE|SyE)\s+(?<kw>\d+)\s+(?:\d+,\d+\s+)?(?<cv>\d+)\s+(?<price>\d{1,3}(?:\.\d{3})*)\s*$/u';

    private const string NOISE_PATTERN = '/BOLET|cve:|Verificable|^Núm\.|Periodo|Datos ficha|comercial|Modelo-Tipo|euros|Inicio|cilind|motor$|^20\d{2}(\s+Val.*)?$|Valor|^Marca:/u';

    /**
     * @return list<array{brand: string, model: string, start_year: int, end_year: int|null, cc: int|null, fuel: string, kw: int, cv: int, price_eur: int}>
     */
    public function parse(string $text): array
    {
        $lines = $this->carSection(explode("\n", $text));

        $rows = [];
        $brand = null;
        $textAbove = null;
        $awaitingNameTail = null;

        foreach ($lines as $line) {
            if (preg_match('/Marca:\s*(.+?)\s*$/u', $line, $brandMatch) === 1) {
                $brand = $brandMatch[1];
                $textAbove = null;
                $awaitingNameTail = null;

                continue;
            }

            if ($brand !== null && preg_match(self::ROW_PATTERN, $line, $match) === 1) {
                [$endYear, $cc] = $this->splitMiddleColumns($match['rest'], (int) $match['start']);
                $model = trim($match['model'] ?? '');

                $rows[] = [
                    'brand' => $brand,
                    'model' => $model !== '' ? $model : (string) $textAbove,
                    'start_year' => (int) $match['start'],
                    'end_year' => $endYear,
                    'cc' => $cc,
                    'fuel' => $match['fuel'],
                    'kw' => (int) $match['kw'],
                    'cv' => (int) $match['cv'],
                    'price_eur' => (int) str_replace('.', '', $match['price']),
                ];

                $awaitingNameTail = $model === '' ? array_key_last($rows) : null;
                $textAbove = null;

                continue;
            }

            $trimmed = trim($line);
            if ($trimmed === '' || preg_match(self::NOISE_PATTERN, $trimmed) === 1) {
                continue;
            }

            if ($awaitingNameTail !== null) {
                $rows[$awaitingNameTail]['model'] = trim($rows[$awaitingNameTail]['model'].' '.$trimmed);
                $awaitingNameTail = null;

                continue;
            }

            $textAbove = $textAbove === null ? $trimmed : $textAbove.' '.$trimmed;
        }

        return $rows;
    }

    /**
     * Only Anexo I up to the motorhome table: motorhomes, motorbikes and quads
     * use other columns and another depreciation table.
     *
     * @param  list<string>  $lines
     * @return list<string>
     */
    private function carSection(array $lines): array
    {
        $start = null;
        $end = count($lines);

        foreach ($lines as $index => $line) {
            if ($start === null && preg_match('/^\s*ANEXO I\s*$/u', $line) === 1) {
                $start = $index;
            } elseif ($start !== null && str_contains($line, 'Precios medios de autocaravanas')) {
                $end = $index;

                break;
            }
        }

        return array_slice($lines, $start ?? 0, $end - ($start ?? 0));
    }

    /**
     * Between the start year and the fuel column sit an optional end year,
     * an optional cc and an optional number of cylinders. An end year is never
     * before the start year; a cylinder count is never above 16.
     *
     * @return array{0: int|null, 1: int|null}
     */
    private function splitMiddleColumns(string $rest, int $startYear): array
    {
        $numbers = array_map('intval', preg_split('/\s+/', trim($rest), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        $endYear = null;
        if ($numbers !== [] && $numbers[0] >= $startYear && $numbers[0] <= $startYear + 50
            && ! (count($numbers) === 2 && $numbers[1] <= 16)) {
            $endYear = array_shift($numbers);
        }

        $cc = $numbers[0] ?? null;

        return [$endYear, $cc !== null && $cc > 16 ? $cc : null];
    }
}
