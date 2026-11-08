<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Persoonlijke tekstmail (geen HTML) aan een verloren lead met de vraag waarom
 * hij niet voor ons koos, inclusief een unieke link naar het feedbackformulier.
 */
final class LostLeadFeedbackMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $feedbackUrl  Persoonlijke link; wordt vooraf bepaald zodat de mail geen bijwerkingen heeft.
     */
    public function __construct(
        public readonly Lead $lead,
        public readonly string $feedbackUrl,
    ) {}

    public function envelope(): Envelope
    {
        $sender = new Address(
            config('services.lead_feedback.from_address'),
            config('services.lead_feedback.from_name'),
        );

        return new Envelope(
            from: $sender,
            replyTo: [$sender],
            subject: 'Mag ik u iets vragen over uw aanvraag?',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.lost-lead-feedback',
            with: [
                'name' => $this->lead->name,
                'kenteken' => $this->lead->kenteken,
                'feedbackUrl' => $this->feedbackUrl,
                'senderName' => config('services.lead_feedback.from_name'),
            ],
        );
    }
}
