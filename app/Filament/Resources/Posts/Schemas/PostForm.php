<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inhoud')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'posts', ignoreRecord: true)
                            ->helperText('Gebruikt in /blog/{slug}. Auto-gevuld vanuit titel; pas alleen aan als je weet wat je doet.'),

                        Textarea::make('excerpt')
                            ->label('Korte samenvatting')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Wordt getoond in de blog-overzichtspagina en in social-share previews.')
                            ->columnSpanFull(),

                        MarkdownEditor::make('content_markdown')
                            ->label('Inhoud (markdown)')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('blog/attachments')
                            ->columnSpanFull(),
                    ]),

                Section::make('Hero-afbeelding')
                    ->schema([
                        FileUpload::make('hero_image_path')
                            ->label('Afbeelding')
                            ->image()
                            ->disk('public')
                            ->directory('blog/heroes')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->helperText('Max 5 MB. Wordt bovenaan de blogpost getoond.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Publicatie')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                Post::STATUS_DRAFT => 'Concept',
                                Post::STATUS_PUBLISHED => 'Gepubliceerd',
                                Post::STATUS_ARCHIVED => 'Gearchiveerd',
                            ])
                            ->default(Post::STATUS_DRAFT)
                            ->required()
                            ->live(),

                        DateTimePicker::make('published_at')
                            ->label('Publicatiedatum')
                            ->seconds(false)
                            ->helperText('Vereist bij status "Gepubliceerd". Leeg of in de toekomst = nog niet zichtbaar op de site.')
                            ->visible(fn (callable $get): bool => $get('status') !== Post::STATUS_DRAFT)
                            ->required(fn (callable $get): bool => $get('status') === Post::STATUS_PUBLISHED),
                    ])
                    ->columns(2),
            ]);
    }
}
