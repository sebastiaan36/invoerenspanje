<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactFormNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

final class ContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();

        Mail::to(config('app.internal_notifications_email', env('INTERNAL_NOTIFICATIONS_EMAIL', 'info@autoinvoerenspanje.nl')))
            ->send(new ContactFormNotification(
                senderName: $data['name'],
                senderEmail: $data['email'],
                senderPhone: $data['phone'] ?? null,
                subject: $data['subject'] ?? null,
                message: $data['message'],
            ));

        return response()->json(['message' => 'Uw bericht is verzonden.']);
    }
}
