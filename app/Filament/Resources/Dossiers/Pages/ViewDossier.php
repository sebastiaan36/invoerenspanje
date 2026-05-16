<?php

namespace App\Filament\Resources\Dossiers\Pages;

use App\Filament\Resources\Dossiers\DossierResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDossier extends ViewRecord
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
