<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Dossier;
use App\Models\Lead;
use App\Services\Leads\LeadConverter;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createDossier')
                ->label('Maak dossier aan')
                ->icon('heroicon-o-folder-plus')
                ->color('success')
                ->visible(fn (Lead $record): bool => $record->dossiers()->doesntExist())
                ->requiresConfirmation()
                ->modalHeading('Dossier aanmaken')
                ->modalDescription('Maakt een klantaccount (indien nog niet bestaand) en een dossier op basis van deze lead. De klant ontvangt direct een welkomstmail met set-password link.')
                ->schema([
                    TextInput::make('service_fee_eur')
                        ->label('Servicebedrag (€)')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('Optioneel — kun je later ook bij het dossier zelf invullen.'),
                ])
                ->action(function (array $data, Lead $record, LeadConverter $converter): void {
                    $dossier = $converter->convert(
                        $record,
                        isset($data['service_fee_eur']) ? (int) $data['service_fee_eur'] : null,
                    );

                    Notification::make()
                        ->title("Dossier #{$dossier->id} aangemaakt")
                        ->body('De klant heeft een welkomstmail ontvangen met een link om een wachtwoord in te stellen.')
                        ->success()
                        ->send();
                }),

            Action::make('viewDossier')
                ->label('Bekijk dossier')
                ->icon('heroicon-o-folder-open')
                ->color('primary')
                ->visible(fn (Lead $record): bool => $record->dossiers()->exists())
                ->url(fn (Lead $record): ?string => ($d = $record->dossiers()->latest()->first())
                    ? \App\Filament\Resources\Dossiers\DossierResource::getUrl('view', ['record' => $d])
                    : null,
                ),

            Action::make('updateStatus')
                ->label('Wijzig status')
                ->icon('heroicon-o-arrow-path')
                ->schema([
                    Select::make('status')
                        ->label('Nieuwe status')
                        ->options([
                            'nieuw' => 'Nieuw',
                            'gecontacteerd' => 'Gecontacteerd',
                            'offerte' => 'Offerte verstuurd',
                            'gewonnen' => 'Gewonnen',
                            'verloren' => 'Verloren',
                        ])
                        ->required(),
                ])
                ->fillForm(fn (Lead $record): array => ['status' => $record->status])
                ->action(function (array $data, Lead $record): void {
                    $record->update(['status' => $data['status']]);

                    Notification::make()
                        ->title('Status bijgewerkt')
                        ->body("Status is gewijzigd naar “{$data['status']}”.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
