<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Mail\LeadReceivedNotification;
use App\Mail\QuoteRequestConfirmation;
use App\Models\Lead;
use App\Services\Quote\VehicleQuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class LeadController extends Controller
{
    public function __construct(
        private readonly VehicleQuoteService $quoteService,
    ) {}

    public function store(LeadRequest $request): JsonResponse
    {
        $attributes = $request->toLeadAttributes();

        // Re-quote server-side voor de snapshots — RDW is gecached, dus geen extra calls.
        $quote = $this->quoteService->quote(
            $attributes['kenteken'],
            residencyChange: (bool) $attributes['residency_change'],
            autonomia: (string) $attributes['autonomia'],
        );

        if ($quote->found()) {
            $attributes['rdw_snapshot_json'] = $quote->rdwSnapshot();
            $attributes['bpm_calculation_json'] = $quote->bpmSnapshot();
            $attributes['import_calculation_json'] = $quote->importSnapshot();
        }

        /** @var Lead $lead */
        $lead = Lead::create($attributes);

        // Mails synchroon, maar elk in een eigen try/catch: een mailprobleem
        // mag de leadregistratie of de gebruiker nooit raken.
        try {
            Mail::to(config('services.internal_notifications.email'))
                ->send(new LeadReceivedNotification($lead));
        } catch (Throwable $e) {
            Log::error('Interne lead-notificatie kon niet worden verstuurd.', [
                'lead_id' => $lead->id,
                'exception' => $e->getMessage(),
            ]);
        }

        try {
            Mail::to($lead->email)
                ->send(new QuoteRequestConfirmation($lead));
        } catch (Throwable $e) {
            Log::error('Bevestigingsmail naar klant kon niet worden verstuurd.', [
                'lead_id' => $lead->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'lead_id' => $lead->id,
            'reference' => sprintf('#%05d', $lead->id),
        ], 201);
    }
}
