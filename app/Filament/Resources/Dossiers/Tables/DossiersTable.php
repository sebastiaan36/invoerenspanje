<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dossiers\Tables;

use App\Models\Dossier;
use App\Services\Packages\ServicePackages;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DossiersTable
{
    public static function configure(Table $table): Table
    {
        $packageOptions = collect(ServicePackages::all())
            ->mapWithKeys(fn ($p) => [$p->slug => $p->name])
            ->all();

        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                IconColumn::make('has_unread_messages')
                    ->label('Nieuw')
                    ->getStateUsing(fn (Dossier $record): bool => $record->messages()
                        ->where('author_role', 'klant')
                        ->whereNull('read_at')
                        ->exists())
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-envelope-open')
                    ->falseColor('gray'),

                TextColumn::make('id')
                    ->label('#')
                    ->formatStateUsing(fn (int $state) => sprintf('#%05d', $state))
                    ->fontFamily('mono')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Dossier::STATUS_CONCEPT => 'gray',
                        Dossier::STATUS_OFFERTE => 'info',
                        Dossier::STATUS_AKKOORD => 'warning',
                        Dossier::STATUS_IN_UITVOERING => 'primary',
                        Dossier::STATUS_AFGEROND => 'success',
                        Dossier::STATUS_GEANNULEERD => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state)))
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Klant')
                    ->searchable(),

                TextColumn::make('kenteken')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('pakket')
                    ->formatStateUsing(fn (string $state) => ServicePackages::findBySlug($state)?->name ?? $state)
                    ->badge()
                    ->color('secondary'),

                TextColumn::make('service_fee_eur')
                    ->label('Service fee')
                    ->money('eur')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Dossier::STATUS_CONCEPT => 'Concept',
                        Dossier::STATUS_OFFERTE => 'Offerte',
                        Dossier::STATUS_AKKOORD => 'Akkoord',
                        Dossier::STATUS_IN_UITVOERING => 'In uitvoering',
                        Dossier::STATUS_AFGEROND => 'Afgerond',
                        Dossier::STATUS_GEANNULEERD => 'Geannuleerd',
                    ])
                    ->multiple(),

                SelectFilter::make('pakket')
                    ->label('Pakket')
                    ->options($packageOptions)
                    ->multiple(),

                Filter::make('has_unread_messages')
                    ->label('Heeft nieuwe klant-berichten')
                    ->query(fn (Builder $query) => $query->whereHas(
                        'messages',
                        fn ($q) => $q->where('author_role', 'klant')->whereNull('read_at'),
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
