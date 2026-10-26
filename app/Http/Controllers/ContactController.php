<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactFormNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

final class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Mail::to(config('services.internal_notifications.email'))
            ->send(new ContactFormNotification(
                senderName: $data['name'],
                senderEmail: $data['email'],
                senderPhone: $data['phone'] ?? null,
                contactSubject: $data['subject'] ?? null,
                contactMessage: $data['message'],
            ));

        // Inertia's form.post() expects a redirect; a JSON body would surface
        // as an error modal and the success state would never show.
        return back()->with('success', 'Uw bericht is verzonden.');
    }
}
