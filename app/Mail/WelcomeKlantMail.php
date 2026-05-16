<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Dossier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class WelcomeKlantMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Dossier $dossier,
        public readonly string $passwordResetToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welkom bij autoinvoerenspanje.nl — uw dossier #{$this->dossier->id} staat klaar",
        );
    }

    public function content(): Content
    {
        $url = url(route('password.reset', [
            'token' => $this->passwordResetToken,
            'email' => $this->user->email,
        ], absolute: false));

        return new Content(
            markdown: 'mail.welcome-klant',
            with: [
                'user' => $this->user,
                'dossier' => $this->dossier,
                'setPasswordUrl' => $url,
            ],
        );
    }
}
