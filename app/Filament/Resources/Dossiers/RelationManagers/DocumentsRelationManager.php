<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dossiers\RelationManagers;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documenten';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('filename')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->formatStateUsing(fn (string $state) => Document::TYPES[$state] ?? $state)
                    ->badge(),
                TextColumn::make('filename')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('size_bytes')
                    ->label('Grootte')
                    ->formatStateUsing(fn (int $state) => $state < 1024 * 1024
                        ? round($state / 1024, 1).' kB'
                        : round($state / (1024 * 1024), 1).' MB',
                    ),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Document::STATUS_AANGEVRAAGD => 'gray',
                        Document::STATUS_GEUPLOAD => 'warning',
                        Document::STATUS_GOEDGEKEURD => 'success',
                        Document::STATUS_AFGEKEURD => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('reviewer.name')
                    ->label('Beoordeeld door')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Geüpload')
                    ->dateTime('d-m-Y H:i'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Document::STATUS_GEUPLOAD => 'In review',
                        Document::STATUS_GOEDGEKEURD => 'Goedgekeurd',
                        Document::STATUS_AFGEKEURD => 'Afgekeurd',
                    ]),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record): string => route('portaal.documents.download', $record))
                    ->openUrlInNewTab(),

                Action::make('approve')
                    ->label('Goedkeuren')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Document $r) => $r->status !== Document::STATUS_GOEDGEKEURD)
                    ->requiresConfirmation()
                    ->action(function (Document $record): void {
                        $record->update([
                            'status' => Document::STATUS_GOEDGEKEURD,
                            'review_note' => null,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Document goedgekeurd.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Afkeuren')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Document $r) => $r->status !== Document::STATUS_AFGEKEURD)
                    ->schema([
                        Textarea::make('review_note')
                            ->label('Reden')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data, Document $record): void {
                        $record->update([
                            'status' => Document::STATUS_AFGEKEURD,
                            'review_note' => $data['review_note'],
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Document afgekeurd.')->warning()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
