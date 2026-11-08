<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Mail\LeadReceivedNotification;
use App\Mail\QuoteRequestConfirmation;
use App\Models\Lead;
use App\Services\Leads\LeadTelegramAlert;
use App\Services\Packages\ServicePackages;
use App\Services\Quote\Dto\VehicleQuote;
use App\Services\Quote\VehicleQuoteService;
use App\Services\Rdw\RdwUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

use function Illuminate\Support\defer;

final class LeadController extends Controller
{
    public function __construct(
        private readonly VehicleQuoteService $quoteService,
        private readonly LeadTelegramAlert $telegramAlert,
    ) {}

    public function store(LeadRequest $request): JsonResponse
    {
        $attributes = $request->toLeadAttributes();

        $attributes = array_merge($attributes, $this->quoteAttributes($attributes));

        /** @var Lead $lead */
        $lead = Lead::create($attributes);

        // Mails pas na het versturen van de response (geen queue-worker nodig op
        // shared hosting), elk in een eigen try/catch: een mailprobleem mag de
        // leadregistratie of de gebruiker nooit raken.
        defer(function () use ($lead): void {
            try {
                $this->telegramAlert->send($lead);
            } catch (Throwable $e) {
                Log::error('Telegram-melding voor nieuwe lead kon niet worden verstuurd.', [
                    'lead_id' => $lead->id,
                    'exception' => $e->getMessage(),
                ]);
            }

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
        });

        return response()->json([
            'ok' => true,
            'lead_id' => $lead->id,
            'reference' => sprintf('#%05d', $lead->id),
        ], 201);
    }

    /**
     * Re-quote server-side for the snapshots and the price indications (RDW is
     * cached, so no extra calls). Any failure — an RDW outage or a bug in a
     * calculator — is reported and the lead is still saved without snapshots;
     * LeadConverter backfills them when the dossier is created.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function quoteAttributes(array $attributes): array
    {
        $empty = [
            'bpm_teruggave_indicatie_eur' => null,
            'import_kosten_indicatie_eur' => null,
            'totaalprijs_indicatie_eur' => null,
        ];

        try {
            $quote = $this->quoteService->quote(
                $attributes['kenteken'],
                residencyChange: (bool) $attributes['residency_change'],
                autonomia: (string) $attributes['autonomia'],
            );

            if (! $quote->found()) {
                return $empty;
            }

            return [
                'rdw_snapshot_json' => $quote->rdwSnapshot(),
                'bpm_calculation_json' => $quote->bpmSnapshot(),
                'import_calculation_json' => $quote->importSnapshot(),
                ...$this->priceIndications($quote, (string) $attributes['package_slug']),
            ];
        } catch (RdwUnavailableException $e) {
            Log::warning('Lead opgeslagen zonder RDW-snapshot: RDW niet bereikbaar.', [
                'kenteken' => $attributes['kenteken'],
                'exception' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            report($e);
            Log::error('Lead opgeslagen zonder snapshots: offerteberekening mislukt.', [
                'kenteken' => $attributes['kenteken'],
                'exception' => $e->getMessage(),
            ]);
        }

        return $empty;
    }

    /**
     * The figures the visitor saw, recomputed from the server-side quote so a
     * tampered request cannot plant other amounts in the lead.
     *
     * @return array{bpm_teruggave_indicatie_eur: int|null, import_kosten_indicatie_eur: int|null, totaalprijs_indicatie_eur: int|null}
     */
    private function priceIndications(VehicleQuote $quote, string $packageSlug): array
    {
        $bpm = $quote->bpmSnapshot();
        $import = $quote->importSnapshot();
        $package = ServicePackages::findBySlug($packageSlug);

        $importTotal = $import === null ? null : (int) round((float) $import['total_eur']);

        return [
            'bpm_teruggave_indicatie_eur' => match (true) {
                $bpm === null => null,
                $bpm['is_eligible'] === true => (int) round((float) $bpm['rest_bpm_eur']),
                default => 0,
            },
            'import_kosten_indicatie_eur' => $importTotal,
            'totaalprijs_indicatie_eur' => $importTotal === null || $package === null
                ? null
                : $package->priceEur + $importTotal,
        ];
    }
}
