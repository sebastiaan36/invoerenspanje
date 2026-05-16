<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Lead;
use App\Services\Packages\ServicePackages;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class LeadReceivedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Lead $lead,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nieuwe lead #{$this->lead->id} — {$this->lead->name} ({$this->lead->kenteken})",
            replyTo: [$this->lead->email],
        );
    }

    public function content(): Content
    {
        $package = ServicePackages::findBySlug($this->lead->package_slug);

        return new Content(
            markdown: 'mail.lead-received',
            with: [
                'lead' => $this->lead,
                'packageName' => $package?->name ?? $this->lead->package_slug,
                'packagePriceEur' => $package?->priceEur,
            ],
        );
    }
}
