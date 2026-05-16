<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dossiers\Pages;

use App\Filament\Resources\Dossiers\DossierResource;
use Filament\Resources\Pages\ListRecords;

class ListDossiers extends ListRecords
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        // Geen Create-actie: dossiers worden via 'Maak dossier aan' op een lead aangemaakt.
        return [];
    }
}
