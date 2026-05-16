<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('woonplaats_spanje')
                    ->required(),
                TextInput::make('expected_move_date'),
                Textarea::make('comment')
                    ->columnSpanFull(),
                TextInput::make('kenteken')
                    ->required(),
                TextInput::make('package_slug')
                    ->required(),
                Toggle::make('residency_change')
                    ->required(),
                TextInput::make('autonomia')
                    ->required()
                    ->default('default'),
                TextInput::make('bpm_teruggave_indicatie_eur')
                    ->numeric(),
                TextInput::make('import_kosten_indicatie_eur')
                    ->numeric(),
                TextInput::make('totaalprijs_indicatie_eur')
                    ->numeric(),
                TextInput::make('source')
                    ->required()
                    ->default('organic'),
                TextInput::make('utm_source'),
                TextInput::make('utm_medium'),
                TextInput::make('utm_campaign'),
                TextInput::make('status')
                    ->required()
                    ->default('nieuw'),
            ]);
    }
}
