<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LookupRequest;
use App\Services\Bpm\BpmCalculator;
use App\Services\Bpm\Dto\BpmInput;
use App\Services\Bpm\Dto\BpmResult;
use App\Services\Rdw\Dto\FuelData;
use App\Services\Rdw\Dto\VehicleData;
use App\Services\Rdw\RdwService;
use Illuminate\Http\JsonResponse;

final class LookupController extends Controller
{
    public function __construct(
        private readonly RdwService $rdw,
        private readonly BpmCalculator $bpm,
    ) {}

    public function __invoke(LookupRequest $request): JsonResponse
    {
        $kenteken = $request->normalizedKenteken();
        $result = $this->rdw->fullLookup($kenteken);

        if (! $result->found()) {
            return response()->json([
                'found' => false,
                'kenteken' => $kenteken,
                'message' => 'Geen voertuig gevonden bij dit kenteken.',
            ], 404);
        }

        $bpmInput = BpmInput::fromLookup($result);
        $bpmResult = $bpmInput !== null ? $this->bpm->calculateRestBpm($bpmInput) : null;

        return response()->json([
            'found' => true,
            'kenteken' => $kenteken,
            'vehicle' => $this->serializeVehicle($result->vehicle),
            'fuel' => $this->serializeFuel($result->fuel),
            'bpm' => $this->serializeBpm($bpmResult),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeVehicle(?VehicleData $vehicle): ?array
    {
        if ($vehicle === null) {
            return null;
        }

        return [
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
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeFuel(?FuelData $fuel): ?array
    {
        if ($fuel === null) {
            return null;
        }

        return [
            'brandstof' => $fuel->brandstofOmschrijving,
            'co2_gecombineerd' => $fuel->co2UitstootGecombineerd,
            'co2_gewogen' => $fuel->co2UitstootGewogen,
            'emissiecode' => $fuel->emissiecodeOmschrijving,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeBpm(?BpmResult $bpm): ?array
    {
        if ($bpm === null) {
            return null;
        }

        return [
            'is_eligible' => $bpm->isEligible,
            'ineligible_reason' => $bpm->ineligibleReason,
            'bruto_bpm_eur' => round($bpm->brutoBpm, 2),
            'rest_bpm_eur' => round($bpm->restBpm, 2),
            'afschrijving_pct' => round($bpm->afschrijvingPercentage, 2),
            'age_months' => $bpm->ageMonths,
            'method' => $bpm->method,
            'notes' => $bpm->notes,
        ];
    }
}
