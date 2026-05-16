<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dossiers\Schemas;

use App\Models\Dossier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DossierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status en planning')
                    ->schema([
                        Select::make('status')
                            ->options([
                                Dossier::STATUS_CONCEPT => 'Concept',
                                Dossier::STATUS_OFFERTE => 'Offerte',
                                Dossier::STATUS_AKKOORD => 'Akkoord',
                                Dossier::STATUS_IN_UITVOERING => 'In uitvoering',
                                Dossier::STATUS_AFGEROND => 'Afgerond',
                                Dossier::STATUS_GEANNULEERD => 'Geannuleerd',
                            ])
                            ->required(),
                        DateTimePicker::make('started_at')->seconds(false),
                        DateTimePicker::make('completed_at')->seconds(false),
                    ])
                    ->columns(3),

                Section::make('Voertuiggegevens (snapshot)')
                    ->schema([
                        TextInput::make('kenteken')->required(),
                        TextInput::make('merk'),
                        TextInput::make('model'),
                        TextInput::make('brandstof'),
                        TextInput::make('co2')->numeric()->label('CO₂ (g/km)'),
                    ])
                    ->columns(3),

                Section::make('Bedragen')
                    ->schema([
                        TextInput::make('bpm_indicatie_eur')->label('BPM-indicatie (€)')->numeric()->minValue(0),
                        TextInput::make('service_fee_eur')->label('Service fee (€)')->numeric()->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }
}
