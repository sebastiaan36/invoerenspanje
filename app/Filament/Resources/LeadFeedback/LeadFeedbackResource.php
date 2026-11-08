<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadFeedback;

use App\Filament\Resources\LeadFeedback\Pages\ListLeadFeedback;
use App\Filament\Resources\LeadFeedback\Pages\ViewLeadFeedback;
use App\Filament\Resources\LeadFeedback\Schemas\LeadFeedbackInfolist;
use App\Filament\Resources\LeadFeedback\Tables\LeadFeedbackTable;
use App\Models\LeadFeedback;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Alleen-lezen overzicht van de feedback van verloren leads. Feedback komt
 * uitsluitend binnen via het publieke feedbackformulier en kan niet worden
 * aangemaakt, gewijzigd of verwijderd.
 */
class LeadFeedbackResource extends Resource
{
    protected static ?string $model = LeadFeedback::class;

    protected static ?string $slug = 'feedback';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?string $navigationLabel = 'Feedback verloren leads';

    protected static ?string $modelLabel = 'feedback';

    protected static ?string $pluralModelLabel = 'feedback';

    public static function getNavigationGroup(): ?string
    {
        return 'Analyse';
    }

    public static function getNavigationBadge(): ?string
    {
        $lastWeek = LeadFeedback::where('created_at', '>=', now()->subWeek())->count();

        return $lastWeek > 0 ? (string) $lastWeek : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadFeedbackInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadFeedbackTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeadFeedback::route('/'),
            'view' => ViewLeadFeedback::route('/{record}'),
        ];
    }
}
