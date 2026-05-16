<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class NewMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\DossierMessage>  $messages
     * @param  string  $recipientRole  'klant' | 'admin'
     */
    public function __construct(
        public readonly Dossier $dossier,
        public readonly Collection $messages,
        public readonly string $recipientRole,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->messages->count();
        $authorRole = $this->messages->first()?->author_role;
        $sender = $authorRole === 'admin' ? 'de uitvoerder' : 'de klant';

        $subject = $count === 1
            ? "Nieuw bericht van {$sender} — dossier #{$this->dossier->id}"
            : "{$count} nieuwe berichten van {$sender} — dossier #{$this->dossier->id}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $url = $this->recipientRole === 'admin'
            ? url("/admin/dossiers/{$this->dossier->id}/chat")
            : url('/portaal/berichten');

        return new Content(
            markdown: 'mail.new-message',
            with: [
                'dossier' => $this->dossier,
                'messages' => $this->messages,
                'recipientRole' => $this->recipientRole,
                'replyUrl' => $url,
            ],
        );
    }
}
