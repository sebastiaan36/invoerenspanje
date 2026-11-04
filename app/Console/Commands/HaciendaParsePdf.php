<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Hacienda\HaciendaPriceImporter;
use App\Services\Hacienda\HaciendaPriceTableParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

/**
 * Run locally once a year when Hacienda publishes the new Orden HAC
 * (needs `pdftotext` from poppler). Commit the CSV; `hacienda:sync`
 * loads it into the database on deploy.
 */
final class HaciendaParsePdf extends Command
{
    protected $signature = 'hacienda:parse-pdf
        {pdf : Pad naar de BOE-PDF met de Orden HAC "precios medios de venta"}
        {--year= : Jaar waarvoor de tabel geldt (standaard: config spain_import.hacienda.table_year)}';

    protected $description = 'Zet de Hacienda-prijstabel (Anexo I) uit de BOE-PDF om naar een CSV in database/data/hacienda';

    public function handle(HaciendaPriceTableParser $parser, HaciendaPriceImporter $importer): int
    {
        $pdf = (string) $this->argument('pdf');
        $tableYear = (int) ($this->option('year') ?: config('spain_import.hacienda.table_year'));

        if (! is_file($pdf)) {
            $this->error("PDF niet gevonden: {$pdf}");

            return self::FAILURE;
        }

        $result = Process::timeout(300)->run(['pdftotext', '-layout', $pdf, '-']);

        if ($result->failed()) {
            $this->error('pdftotext mislukt (installeer poppler): '.$result->errorOutput());

            return self::FAILURE;
        }

        $rows = $parser->parse($result->output());

        if ($rows === []) {
            $this->error('Geen prijsregels gevonden in de PDF.');

            return self::FAILURE;
        }

        $path = $importer->writeCsv($tableYear, $rows);

        $this->info(sprintf('%d regels (%d merken) geschreven naar %s', count($rows), count(array_unique(array_column($rows, 'brand'))), $path));

        return self::SUCCESS;
    }
}
