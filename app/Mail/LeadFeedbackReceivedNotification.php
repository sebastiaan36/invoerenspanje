<?php

declare(strict_types=1);

namespace App\Mail;

use App\Filament\Resources\LeadFeedback\LeadFeedbackResource;
use App\Models\LeadFeedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Interne melding zodra een verloren lead feedback achterlaat.
 */
final class LeadFeedbackReceivedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly LeadFeedback $feedback,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Feedback van verloren lead: {$this->feedback->lead_name}",
            replyTo: [$this->feedback->lead_email],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.lead-feedback-received',
            with: [
                'feedback' => $this->feedback,
                'feedbackUrl' => LeadFeedbackResource::getUrl('view', ['record' => $this->feedback]),
            ],
        );
    }
}
