<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withExists(User::protectingRelations()))
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mailadres')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telefoon')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Beheerder',
                        'klant' => 'Klant',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'admin' => 'Beheerder',
                        'klant' => 'Klant',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->deletionBlockedReason(self::actor()) !== null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords(function (User $record): Response {
                            $reason = $record->deletionBlockedReason(self::actor());

                            return $reason === null ? Response::allow() : Response::deny($reason);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function actor(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
