<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;

class PostsTable
{
    /**
     * Artikelen uit resources/content/blog komen bij de volgende blog:sync terug;
     * verwijder het Markdown-bestand om ze weg te halen (dan worden ze gearchiveerd).
     */
    public const string FILE_MANAGED_DELETE_MESSAGE = 'Artikelen uit een Markdown-bestand kun je niet verwijderen: ze komen bij de volgende sync terug. Verwijder het bestand in resources/content/blog, dan wordt het artikel gearchiveerd.';

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                ImageColumn::make('hero_image_path')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(48),

                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (Post $record) => $record->slug),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Post::STATUS_PUBLISHED => 'success',
                        Post::STATUS_ARCHIVED => 'gray',
                        Post::STATUS_DRAFT => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Post::STATUS_PUBLISHED => 'Gepubliceerd',
                        Post::STATUS_ARCHIVED => 'Gearchiveerd',
                        Post::STATUS_DRAFT => 'Concept',
                        default => $state,
                    }),

                TextColumn::make('published_at')
                    ->label('Publicatiedatum')
                    ->dateTime('d-m-Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('author.name')
                    ->label('Auteur')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Laatst gewijzigd')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Post::STATUS_DRAFT => 'Concept',
                        Post::STATUS_PUBLISHED => 'Gepubliceerd',
                        Post::STATUS_ARCHIVED => 'Gearchiveerd',
                    ])
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->hidden(fn (Post $record): bool => $record->isFileManaged()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords(fn (Post $record): Response => $record->isFileManaged()
                            ? Response::deny(self::FILE_MANAGED_DELETE_MESSAGE)
                            : Response::allow()),
                ]),
            ]);
    }
}
