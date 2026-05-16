<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Tables;

use App\Services\Packages\ServicePackages;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        $packageOptions = collect(ServicePackages::all())
            ->mapWithKeys(fn ($p) => [$p->slug => $p->name])
            ->all();

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Aangevraagd')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nieuw' => 'warning',
                        'gecontacteerd' => 'info',
                        'offerte' => 'primary',
                        'gewonnen' => 'success',
                        'verloren' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('Telefoon')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kenteken')
                    ->searchable()
                    ->fontFamily('mono')
                    ->copyable(),

                TextColumn::make('package_slug')
                    ->label('Pakket')
                    ->formatStateUsing(fn (string $state) => ServicePackages::findBySlug($state)?->name ?? $state)
                    ->badge()
                    ->color('secondary'),

                TextColumn::make('totaalprijs_indicatie_eur')
                    ->label('Totaalprijs')
                    ->money('eur')
                    ->sortable(),

                TextColumn::make('woonplaats_spanje')
                    ->label('Regio')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('source')
                    ->label('Bron')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'nieuw' => 'Nieuw',
                        'gecontacteerd' => 'Gecontacteerd',
                        'offerte' => 'Offerte verstuurd',
                        'gewonnen' => 'Gewonnen',
                        'verloren' => 'Verloren',
                    ])
                    ->multiple(),

                SelectFilter::make('package_slug')
                    ->label('Pakket')
                    ->options($packageOptions)
                    ->multiple(),

                SelectFilter::make('source')
                    ->label('Bron')
                    ->options([
                        'organic' => 'Organic',
                        'ads' => 'Ads',
                        'referral' => 'Referral',
                    ])
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
