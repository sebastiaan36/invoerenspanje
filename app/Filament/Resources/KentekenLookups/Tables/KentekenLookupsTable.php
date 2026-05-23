<?php

declare(strict_types=1);

namespace App\Filament\Resources\KentekenLookups\Tables;

use App\Models\KentekenLookup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KentekenLookupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kenteken')
                    ->label('Kenteken')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('merk')
                    ->label('Merk')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('bouwjaar')
                    ->label('Bouwjaar')
                    ->placeholder('—'),

                TextColumn::make('brandstof')
                    ->label('Brandstof')
                    ->placeholder('—')
                    ->badge()
                    ->color(fn (?string $state): string => match (true) {
                        str_contains(strtolower((string) $state), 'elektr') => 'success',
                        str_contains(strtolower((string) $state), 'benzine') => 'warning',
                        str_contains(strtolower((string) $state), 'diesel') => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('page')
                    ->label('Pagina')
                    ->placeholder('—')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? parse_url($state, PHP_URL_PATH) ?? $state
                        : '—'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP-adres')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Tijdstip')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record): string => $record->created_at->format('d-m-Y H:i:s')),
            ])
            ->filters([
                SelectFilter::make('brandstof')
                    ->label('Brandstof')
                    ->options(fn () => KentekenLookup::whereNotNull('brandstof')
                        ->distinct()
                        ->pluck('brandstof', 'brandstof')
                        ->toArray()
                    ),

                Filter::make('niet_gevonden')
                    ->label('Niet gevonden')
                    ->query(fn (Builder $query) => $query->whereNull('merk')),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}
