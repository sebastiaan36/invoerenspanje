<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LookupRequest;
use App\Services\Quote\VehicleQuoteService;
use Illuminate\Http\JsonResponse;

final class LookupController extends Controller
{
    public function __construct(
        private readonly VehicleQuoteService $quoteService,
    ) {}

    public function __invoke(LookupRequest $request): JsonResponse
    {
        $quote = $this->quoteService->quote(
            $request->normalizedKenteken(),
            residencyChange: $request->residencyChange(),
            autonomia: $request->autonomia(),
        );

        if (! $quote->found()) {
            return response()->json([
                'found' => false,
                'kenteken' => $quote->kenteken,
                'message' => 'Geen voertuig gevonden bij dit kenteken.',
            ], 404);
        }

        $rdw = $quote->rdwSnapshot();

        return response()->json([
            'found' => true,
            'kenteken' => $quote->kenteken,
            'vehicle' => $rdw['vehicle'],
            'fuel' => $rdw['fuel'],
            'bpm' => $quote->bpmSnapshot(),
            'import_costs' => $quote->importSnapshot(),
            'net_effect_eur' => $quote->netEffectEur(),
        ]);
    }
}
