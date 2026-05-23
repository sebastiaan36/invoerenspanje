<?php

declare(strict_types=1);

namespace App\Services\Quote\Dto;

use App\Services\Bpm\Dto\BpmResult;
use App\Services\Rdw\Dto\VehicleLookupResult;
use App\Services\SpainImport\Dto\SpainImportResult;

/**
 * Bundelt het volledige resultaat van één offerte-berekening voor een kenteken.
 * Wordt gebruikt door zowel de live calculator-endpoint als de lead-opslag.
 */
final readonly class VehicleQuote
{
    public function __construct(
        public string $kenteken,
        public VehicleLookupResult $rdw,
        public ?BpmResult $bpm,
        public ?SpainImportResult $spainImport,
        public ?SpainImportResult $spainImportWithoutExemption,
    ) {}

    public function found(): bool
    {
        return $this->rdw->found();
    }

    public function netEffectEur(): ?float
    {
        if ($this->spainImport === null) {
            return null;
        }
        $bpmRest = $this->bpm !== null && $this->bpm->isEligible ? $this->bpm->restBpm : 0.0;

        return round($bpmRest - $this->spainImport->totalEur, 2);
    }

    /**
     * @return array<string, mixed>
     */
    public function rdwSnapshot(): array
    {
        $vehicle = $this->rdw->vehicle;
        $fuel = $this->rdw->fuel;

        return [
            'kenteken' => $this->kenteken,
            'vehicle' => $vehicle === null ? null : [
                'kenteken' => $vehicle->kenteken,
                'voertuigsoort' => $vehicle->voertuigsoort,
                'merk' => $vehicle->merk,
                'handelsbenaming' => $vehicle->handelsbenaming,
                'inrichting' => $vehicle->inrichting,
                'eerste_kleur' => $vehicle->eersteKleur,
                'aantal_zitplaatsen' => $vehicle->aantalZitplaatsen,
                'datum_eerste_toelating' => $vehicle->datumEersteToelating?->toDateString(),
                'datum_eerste_tenaamstelling_nl' => $vehicle->datumEersteTenaamstellingNl?->toDateString(),
                'vervaldatum_apk' => $vehicle->vervaldatumApk?->toDateString(),
                'massa_ledig_voertuig' => $vehicle->massaLedigVoertuig,
                'cilinderinhoud' => $vehicle->cilinderinhoud,
                'catalogusprijs' => $vehicle->catalogusprijs,
                'wam_verzekerd' => $vehicle->wamVerzekerd,
            ],
            'fuel' => $fuel === null ? null : [
                'brandstof' => $fuel->brandstofOmschrijving,
                'co2_gecombineerd' => $fuel->co2UitstootGecombineerd,
                'co2_gewogen' => $fuel->co2UitstootGewogen,
                'co2_wltp_gecombineerd' => $fuel->co2WltpGecombineerd,
                'co2_wltp_gewogen' => $fuel->co2WltpGewogen,
                'emissiecode' => $fuel->emissiecodeOmschrijving,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function bpmSnapshot(): ?array
    {
        if ($this->bpm === null) {
            return null;
        }

        return [
            'is_eligible' => $this->bpm->isEligible,
            'ineligible_reason' => $this->bpm->ineligibleReason,
            'bruto_bpm_eur' => round($this->bpm->brutoBpm, 2),
            'rest_bpm_eur' => round($this->bpm->restBpm, 2),
            'afschrijving_pct' => round($this->bpm->afschrijvingPercentage, 2),
            'age_months' => $this->bpm->ageMonths,
            'method' => $this->bpm->method,
            'notes' => $this->bpm->notes,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function importSnapshot(): ?array
    {
        if ($this->spainImport === null) {
            return null;
        }

        return [
            'iedmt_eur' => $this->spainImport->iedmtEur,
            'iedmt_without_exemption_eur' => $this->spainImportWithoutExemption?->iedmtEur ?? $this->spainImport->iedmtEur,
            'iedmt_rate_pct' => $this->spainImport->iedmtRatePct,
            'iedmt_exempt' => $this->spainImport->iedmtExempt,
            'iedmt_exempt_reason' => $this->spainImport->iedmtExemptReason,
            'estimated_market_value_eur' => $this->spainImport->estimatedMarketValueEur,
            'fixed_costs' => array_map(
                fn ($c) => ['key' => $c->key, 'label' => $c->label, 'amount_eur' => $c->amountEur],
                $this->spainImport->fixedCosts,
            ),
            'fixed_costs_total_eur' => $this->spainImport->fixedCostsTotalEur,
            'total_eur' => $this->spainImport->totalEur,
            'autonomia' => $this->spainImport->autonomia,
            'notes' => $this->spainImport->notes,
        ];
    }
}
