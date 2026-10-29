<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review')
                    ->description('Alleen echte reviews, met toestemming van de klant (bijv. overgenomen uit Google-reviews).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Naam')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Zoals de klant genoemd wil worden, bijv. "Jan en Marieke" of "Peter V."'),

                        TextInput::make('location')
                            ->label('Woonplaats')
                            ->maxLength(100)
                            ->placeholder('Marbella'),

                        ToggleButtons::make('rating')
                            ->label('Sterren')
                            ->required()
                            ->inline()
                            ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                            ->default(5),

                        DatePicker::make('reviewed_on')
                            ->label('Datum review')
                            ->required()
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->maxDate(now())
                            ->default(now())
                            ->helperText('Op de site getoond als maand en jaar.'),

                        TextInput::make('source')
                            ->label('Bron')
                            ->required()
                            ->maxLength(50)
                            ->default('Google')
                            ->datalist(['Google', 'Trustpilot', 'Facebook', 'E-mail', 'WhatsApp']),

                        Textarea::make('text')
                            ->label('Reviewtekst')
                            ->required()
                            ->rows(5)
                            ->maxLength(600)
                            ->helperText('Letterlijk overnemen. Kort is beter: 1 tot 3 zinnen lezen het prettigst.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Publicatie')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Tonen op de homepage')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->label('Volgorde')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Laag eerst. Je kunt ook slepen in het overzicht.'),
                    ]),
            ]);
    }
}
