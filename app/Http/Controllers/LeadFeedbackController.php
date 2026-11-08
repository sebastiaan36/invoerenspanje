<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadFeedbackRequest;
use App\Mail\LeadFeedbackReceivedNotification;
use App\Models\Lead;
use App\Models\LeadFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Feedbackpagina voor verloren leads, bereikbaar via de unieke link uit de feedbackmail.
 */
final class LeadFeedbackController extends Controller
{
    public function show(string $token): Response
    {
        $lead = $this->findLead($token);

        return Inertia::render('Feedback', [
            'token' => $token,
            'firstName' => strtok($lead->name, ' ') ?: $lead->name,
            'submitted' => $lead->feedback()->exists(),
        ]);
    }

    public function store(StoreLeadFeedbackRequest $request, string $token): RedirectResponse
    {
        $lead = $this->findLead($token);

        $feedback = LeadFeedback::recordFor($lead, $request->validated('message'));

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
