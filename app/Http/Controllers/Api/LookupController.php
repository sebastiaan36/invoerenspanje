<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LookupRequest;
use App\Services\Bpm\BpmCalculator;
use App\Services\Bpm\Dto\BpmIndication;
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

        $bpm = $this->bpm->calculate($result);

        return response()->json([
            'found' => true,
            'kenteken' => $kenteken,
            'vehicle' => $this->serializeVehicle($result->vehicle),
            'fuel' => $this->serializeFuel($result->fuel),
            'bpm' => $this->serializeBpm($bpm),
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
    private function serializeBpm(?BpmIndication $bpm): ?array
    {
        if ($bpm === null) {
            return null;
        }

        return [
            'estimated_refund_eur' => $bpm->estimatedRefundEur,
            'original_bpm_eur' => $bpm->originalBpmEur,
            'depreciation_factor' => round($bpm->depreciationFactor, 4),
            'inputs' => $bpm->inputs,
            'notes' => $bpm->notes,
        ];
    }
}
