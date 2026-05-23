<?php

declare(strict_types=1);

namespace App\Filament\Resources\KentekenLookups\Pages;

use App\Filament\Resources\KentekenLookups\KentekenLookupResource;
use Filament\Resources\Pages\ListRecords;

class ListKentekenLookups extends ListRecords
{
    protected static string $resource = KentekenLookupResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
