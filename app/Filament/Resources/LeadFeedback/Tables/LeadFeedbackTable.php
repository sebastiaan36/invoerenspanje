<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadFeedback\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadFeedbackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Ontvangen')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                TextColumn::make('lead_name')
                    ->label('Naam')
                    ->searchable(),

                TextColumn::make('kenteken')
                    ->fontFamily('mono')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('message')
                    ->label('Feedback')
                    ->searchable()
                    ->limit(120)
                    ->wrap(),

                TextColumn::make('lead_email')
                    ->label('E-mail')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            // Geen bulk-acties: feedback kan niet worden verwijderd.
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
