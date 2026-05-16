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
use Illuminate\Support\Facades\Mail;

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

        // Twee mails — kan later naar de queue, voor nu sync.
        Mail::to(config('services.internal_notifications.email'))
            ->send(new LeadReceivedNotification($lead));

        Mail::to($lead->email)
            ->send(new QuoteRequestConfirmation($lead));

        return response()->json([
            'ok' => true,
            'lead_id' => $lead->id,
            'reference' => sprintf('#%05d', $lead->id),
        ], 201);
    }
}
