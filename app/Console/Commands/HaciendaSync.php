<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Hacienda\HaciendaPriceImporter;
use Illuminate\Console\Command;
use RuntimeException;

final class HaciendaSync extends Command
{
    protected $signature = 'hacienda:sync {--force : Ook importeren als de CSV niet gewijzigd is}';

    protected $description = 'Laad de Hacienda-prijstabellen uit database/data/hacienda in de database';

    public function handle(HaciendaPriceImporter $importer): int
    {
        try {
            $result = $importer->sync((bool) $this->option('force'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($result === []) {
            $this->warn('Geen CSV-bestanden gevonden.');
        }

        foreach ($result as $tableYear => ['status' => $status, 'rows' => $rows]) {
            $label = $status === 'imported' ? 'Geïmporteerd' : 'Ongewijzigd';
            $this->info("{$tableYear}: {$label} ({$rows} regels)");
        }

        return self::SUCCESS;
    }
}
