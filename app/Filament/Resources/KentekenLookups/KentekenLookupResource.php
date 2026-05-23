<?php

declare(strict_types=1);

namespace App\Filament\Resources\KentekenLookups;

use App\Filament\Resources\KentekenLookups\Pages\ListKentekenLookups;
use App\Filament\Resources\KentekenLookups\Tables\KentekenLookupsTable;
use App\Models\KentekenLookup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KentekenLookupResource extends Resource
{
    protected static ?string $model = KentekenLookup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'Kenteken lookups';

    protected static ?string $modelLabel = 'lookup';

    protected static ?string $pluralModelLabel = 'kenteken lookups';

    public static function getNavigationGroup(): ?string
    {
        return 'Analyse';
    }

    public static function getNavigationBadge(): ?string
    {
        $today = KentekenLookup::whereDate('created_at', today())->count();

        return $today > 0 ? (string) $today : null;
    }

    public static function table(Table $table): Table
    {
        return KentekenLookupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKentekenLookups::route('/'),
        ];
    }
}
