<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadFeedbackRequest;
use App\Mail\LeadFeedbackReceivedNotification;
use App\Models\Lead;
use App\Models\LeadFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Feedbackpagina voor verloren leads, bereikbaar via de unieke link uit de feedbackmail.
 */
final class LeadFeedbackController extends Controller
{
    public const string EXPIRED_MESSAGE = 'Deze link is niet meer geldig. Mail ons gerust als u nog iets kwijt wilt.';

    public const string LIMIT_MESSAGE = 'Dank u wel, wij hebben uw berichten ontvangen. Wilt u nog iets toevoegen? Mail ons dan gerust.';

    public function show(string $token): Response
    {
        $lead = $this->findLead($token);

        return Inertia::render('Feedback', [
            'token' => $token,
            'firstName' => strtok($lead->name, ' ') ?: $lead->name,
            'submitted' => $lead->feedback()->exists(),
            'linkExpired' => ! $lead->hasActiveFeedbackLink(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(StoreLeadFeedbackRequest $request, string $token): RedirectResponse
    {
        $lead = $this->findLead($token);

        $feedback = DB::transaction(function () use ($lead, $request): LeadFeedback {
            // Rij vergrendelen, zodat gelijktijdige verzoeken het maximum niet kunnen omzeilen.
            $lockedLead = Lead::query()->whereKey($lead->getKey())->lockForUpdate()->firstOrFail();

            if (! $lockedLead->hasActiveFeedbackLink()) {
                throw ValidationException::withMessages(['message' => self::EXPIRED_MESSAGE]);
            }

            if ($lockedLead->hasReachedFeedbackLimit()) {
                throw ValidationException::withMessages(['message' => self::LIMIT_MESSAGE]);
            }

            return LeadFeedback::recordFor($lockedLead, $request->validated('message'));
        });

        try {
            Mail::to(config('services.internal_notifications.email'))
                ->send(new LeadFeedbackReceivedNotification($feedback));
        } catch (Throwable $exception) {
            // De feedback staat al in de database; een mislukte melding mag de bezoeker niet raken.
            Log::warning('Feedbackmelding versturen mislukt', ['lead_id' => $lead->id, 'error' => $exception->getMessage()]);
        }

        return back();
    }

    private function findLead(string $token): Lead
    {
        return Lead::query()->where('feedback_token', $token)->firstOrFail();
    }
}
