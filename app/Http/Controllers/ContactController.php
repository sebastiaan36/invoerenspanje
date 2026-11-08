<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactFormNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class ContactController extends Controller
{
    /**
     * Queue the notification mail and always confirm to the visitor.
     *
     * Should even queueing fail, the full message goes to the log so it is
     * never lost, and the visitor still gets the success state instead of a 500.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            Mail::to(config('services.internal_notifications.email'))
                ->queue(new ContactFormNotification(
                    senderName: $data['name'],
                    senderEmail: $data['email'],
                    senderPhone: $data['phone'] ?? null,
                    contactSubject: $data['subject'] ?? null,
                    contactMessage: $data['message'],
                ));
        } catch (Throwable $exception) {
            Log::error('Contactformulier: mail kon niet in de wachtrij worden gezet.', [
                'exception' => $exception->getMessage(),
                'contact' => $data,
            ]);
        }

        // Inertia's form.post() expects a redirect; a JSON body would surface
        // as an error modal and the success state would never show.
        return back()->with('success', 'Uw bericht is verzonden.');
    }
}
