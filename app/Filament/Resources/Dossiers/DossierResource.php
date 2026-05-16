<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dossiers;

use App\Filament\Resources\Dossiers\Pages\ChatDossier;
use App\Filament\Resources\Dossiers\Pages\EditDossier;
use App\Filament\Resources\Dossiers\Pages\ListDossiers;
use App\Filament\Resources\Dossiers\Pages\ViewDossier;
use App\Filament\Resources\Dossiers\Schemas\DossierForm;
use App\Filament\Resources\Dossiers\Schemas\DossierInfolist;
use App\Filament\Resources\Dossiers\Tables\DossiersTable;
use App\Models\Dossier;
use BackedEnum;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DossierResource extends Resource
{
    protected static ?string $model = Dossier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $navigationLabel = 'Dossiers';

    protected static ?string $modelLabel = 'dossier';

    protected static ?string $pluralModelLabel = 'dossiers';

    protected static ?string $recordTitleAttribute = 'kenteken';

    public static function getNavigationBadge(): ?string
    {
        $count = Dossier::query()
            ->whereHas('messages', fn ($q) => $q->where('author_role', 'klant')->whereNull('read_at'))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return DossierForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DossierInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DossiersTable::configure($table);
    }

    public static function getRelations(): array
    {
        // Berichten zitten nu op een eigen sub-tab (ChatDossier), niet meer in de relation manager.
        return [
            \App\Filament\Resources\Dossiers\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    /**
     * @return array<int, \Filament\Schemas\Components\NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewDossier::class,
            EditDossier::class,
            ChatDossier::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDossiers::route('/'),
            'view' => ViewDossier::route('/{record}'),
            'edit' => EditDossier::route('/{record}/edit'),
            'chat' => ChatDossier::route('/{record}/chat'),
        ];
    }
}
