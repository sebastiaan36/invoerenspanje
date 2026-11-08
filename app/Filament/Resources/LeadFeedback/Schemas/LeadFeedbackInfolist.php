<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadFeedback\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadFeedbackInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feedback')
                    ->schema([
                        TextEntry::make('message')
                            ->label('Bericht van de klant')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Ontvangen op')
                            ->dateTime('d-m-Y H:i'),
                    ])
                    ->columnSpanFull(),

                Section::make('Klant')
                    ->schema([
                        TextEntry::make('lead_name')->label('Naam'),
                        TextEntry::make('lead_email')
                            ->label('E-mail')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('kenteken')
                            ->fontFamily('mono')
                            ->placeholder('—'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
