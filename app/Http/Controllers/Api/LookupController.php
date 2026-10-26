<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LookupRequest;
use App\Models\KentekenLookup;
use App\Services\Quote\VehicleQuoteService;
use App\Services\Rdw\RdwUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LookupController extends Controller
{
    public function __construct(
        private readonly VehicleQuoteService $quoteService,
    ) {}

    public function __invoke(LookupRequest $request): JsonResponse
    {
        try {
            $quote = $this->quoteService->quote(
                $request->normalizedKenteken(),
                residencyChange: $request->residencyChange(),
                autonomia: $request->autonomia(),
            );
        } catch (RdwUnavailableException) {
            // Not "kenteken unknown": the visitor should retry, not doubt their plate.
            return response()->json([
                'found' => false,
                'kenteken' => $request->normalizedKenteken(),
                'message' => 'De RDW is op dit moment niet bereikbaar. Probeer het over een paar minuten opnieuw.',
            ], 503);
        }

        if (! $quote->found()) {
            KentekenLookup::create([
                'kenteken' => $quote->kenteken,
                'ip_address' => $request->ip(),
                'page' => $this->referer($request),
            ]);

            return response()->json([
                'found' => false,
                'kenteken' => $quote->kenteken,
                'message' => 'Geen voertuig gevonden bij dit kenteken.',
            ], 404);
        }

        $rdw = $quote->rdwSnapshot();

        KentekenLookup::create([
            'kenteken' => $quote->kenteken,
            'merk' => $rdw['vehicle']['merk'] ?? null,
            'model' => $rdw['vehicle']['handelsbenaming'] ?? null,
            'bouwjaar' => isset($rdw['vehicle']['bouwjaar']) ? (int) $rdw['vehicle']['bouwjaar'] : null,
            'brandstof' => $rdw['fuel']['brandstof_omschrijving'] ?? null,
            'ip_address' => $request->ip(),
            'page' => $this->referer($request),
        ]);

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

    /**
     * The Referer header is client-controlled and can exceed the 255-char
     * column, which would turn a lookup into a 500 under strict MySQL.
     */
    private function referer(Request $request): ?string
    {
        $referer = $request->header('Referer');

        return $referer === null ? null : mb_substr($referer, 0, 255);
    }
}
