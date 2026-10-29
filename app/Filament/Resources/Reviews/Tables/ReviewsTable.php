<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            // Drag to set the order on the homepage.
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->description(fn (Review $record): ?string => $record->location),

                TextColumn::make('rating')
                    ->label('Sterren')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning'),

                TextColumn::make('text')
                    ->label('Review')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('reviewed_on')
                    ->label('Datum')
                    ->date('M Y')
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Bron')
                    ->badge()
                    ->color('gray'),

                ToggleColumn::make('is_published')
                    ->label('Op homepage'),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Op homepage'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
