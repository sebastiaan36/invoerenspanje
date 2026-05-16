<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Services\Packages\ServicePackages;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'nieuw' => 'warning',
                                'gecontacteerd' => 'info',
                                'offerte' => 'primary',
                                'gewonnen' => 'success',
                                'verloren' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label('Aangevraagd op')
                            ->dateTime('d-m-Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Laatst gewijzigd')
                            ->dateTime('d-m-Y H:i'),
                    ])
                    ->columns(3),

                Section::make('Klant')
                    ->schema([
                        TextEntry::make('name')->label('Naam'),
                        TextEntry::make('email')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('phone')
                            ->label('Telefoon')
                            ->copyable()
                            ->icon('heroicon-o-phone'),
                        TextEntry::make('woonplaats_spanje')
                            ->label('Regio Spanje'),
                        TextEntry::make('expected_move_date')
                            ->label('Verwachte verhuisdatum')
                            ->placeholder('—'),
                        TextEntry::make('comment')
                            ->label('Opmerking van de klant')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Voertuig en pakket')
                    ->schema([
                        TextEntry::make('kenteken')
                            ->fontFamily('mono')
                            ->copyable(),
                        TextEntry::make('package_slug')
                            ->label('Pakket')
                            ->formatStateUsing(fn (string $state) => ServicePackages::findBySlug($state)?->name ?? $state)
                            ->badge()
                            ->color('secondary'),
                        IconEntry::make('residency_change')
                            ->label('Verhuizing residencia habitual')
                            ->boolean(),
                        TextEntry::make('autonomia')
                            ->label('Autonomía'),
                    ])
                    ->columns(2),

                Section::make('Indicatie uit calculator')
                    ->schema([
                        TextEntry::make('bpm_teruggave_indicatie_eur')
                            ->label('BPM-teruggave NL')
                            ->money('eur')
                            ->placeholder('—'),
                        TextEntry::make('import_kosten_indicatie_eur')
                            ->label('Spaanse importkosten')
                            ->money('eur')
                            ->placeholder('—'),
                        TextEntry::make('totaalprijs_indicatie_eur')
                            ->label('Totaalprijs')
                            ->money('eur')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                Section::make('Tracking')
                    ->schema([
                        TextEntry::make('source')
                            ->label('Bron')
                            ->badge(),
                        TextEntry::make('utm_source')->label('UTM source')->placeholder('—'),
                        TextEntry::make('utm_medium')->label('UTM medium')->placeholder('—'),
                        TextEntry::make('utm_campaign')->label('UTM campaign')->placeholder('—'),
                    ])
                    ->columns(4)
                    ->collapsed(),

                Section::make('Voertuiggegevens uit RDW (snapshot)')
                    ->schema([
                        TextEntry::make('rdw_snapshot_json.vehicle.merk')->label('Merk')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.handelsbenaming')->label('Model')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.voertuigsoort')->label('Soort')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.inrichting')->label('Inrichting')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.eerste_kleur')->label('Kleur')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.aantal_zitplaatsen')->label('Zitplaatsen')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.datum_eerste_toelating')->label('Eerste toelating')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.datum_eerste_tenaamstelling_nl')->label('Tenaamstelling NL')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.vervaldatum_apk')->label('APK vervalt')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.massa_ledig_voertuig')->label('Leeggewicht (kg)')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.cilinderinhoud')->label('Cilinderinhoud (cc)')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.vehicle.catalogusprijs')->label('Catalogusprijs')->money('eur')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.fuel.brandstof')->label('Brandstof')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.fuel.co2_gecombineerd')->label('CO₂ gecombineerd (g/km)')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.fuel.co2_gewogen')->label('CO₂ gewogen (PHEV)')->placeholder('—'),
                        TextEntry::make('rdw_snapshot_json.fuel.emissiecode')->label('Emissieklasse')->placeholder('—'),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->visible(fn ($record) => filled($record?->rdw_snapshot_json)),

                Section::make('BPM-berekening (snapshot)')
                    ->schema([
                        TextEntry::make('bpm_calculation_json.is_eligible')
                            ->label('In aanmerking')
                            ->formatStateUsing(fn ($state) => $state ? 'Ja' : 'Nee')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                        TextEntry::make('bpm_calculation_json.ineligible_reason')->label('Reden uitsluiting')->placeholder('—'),
                        TextEntry::make('bpm_calculation_json.method')->label('Methode'),
                        TextEntry::make('bpm_calculation_json.bruto_bpm_eur')->label('Bruto BPM')->money('eur'),
                        TextEntry::make('bpm_calculation_json.afschrijving_pct')
                            ->label('Afschrijving')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '').' %' : '—'),
                        TextEntry::make('bpm_calculation_json.age_months')->label('Leeftijd (mnd)'),
                        TextEntry::make('bpm_calculation_json.rest_bpm_eur')->label('Rest-BPM (teruggave)')->money('eur'),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->visible(fn ($record) => filled($record?->bpm_calculation_json)),

                Section::make('Spaanse import-berekening (snapshot)')
                    ->schema([
                        TextEntry::make('import_calculation_json.iedmt_rate_pct')
                            ->label('IEDMT-tarief')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '').' %' : '—'),
                        TextEntry::make('import_calculation_json.iedmt_eur')->label('IEDMT')->money('eur'),
                        TextEntry::make('import_calculation_json.iedmt_without_exemption_eur')
                            ->label('IEDMT zonder vrijstelling')
                            ->money('eur')
                            ->placeholder('—'),
                        TextEntry::make('import_calculation_json.iedmt_exempt')
                            ->label('Vrijgesteld')
                            ->formatStateUsing(fn ($state) => $state ? 'Ja' : 'Nee')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'gray'),
                        TextEntry::make('import_calculation_json.iedmt_exempt_reason')
                            ->label('Reden vrijstelling')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('import_calculation_json.estimated_market_value_eur')->label('Geschatte marktwaarde')->money('eur'),
                        TextEntry::make('import_calculation_json.fixed_costs_total_eur')->label('Vaste kosten totaal')->money('eur'),
                        TextEntry::make('import_calculation_json.total_eur')->label('Totaal Spanje')->money('eur'),
                        TextEntry::make('fixed_costs_breakdown')
                            ->label('Vaste kosten breakdown')
                            ->state(function ($record): string {
                                $costs = $record?->import_calculation_json['fixed_costs'] ?? null;

                                return is_array($costs) && $costs !== []
                                    ? collect($costs)->map(fn ($c) => "{$c['label']}: € ".number_format((float) $c['amount_eur'], 0, ',', '.'))->implode(' · ')
                                    : '—';
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->visible(fn ($record) => filled($record?->import_calculation_json)),
            ]);
    }
}
