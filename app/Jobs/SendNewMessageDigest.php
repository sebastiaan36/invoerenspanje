<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewMessageMail;
use App\Models\Dossier;
use App\Models\DossierMessage;
use App\Services\Messages\MessageNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendNewMessageDigest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $dossierId,
        public readonly string $authorRole,
    ) {}

    public function handle(MessageNotifier $notifier): void
    {
        $dossier = Dossier::with('user')->find($this->dossierId);
        if ($dossier === null) {
            return;
        }

        $messages = DossierMessage::where('dossier_id', $this->dossierId)
            ->where('author_role', $this->authorRole)
            ->whereNull('notified_at')
            ->with(['author:id,name', 'attachments'])
            ->oldest()
            ->get();

        if ($messages->isEmpty()) {
            return;
        }

        $recipientRole = $notifier->recipientRoleFor($this->authorRole);
        $recipientEmail = match ($recipientRole) {
            'admin' => config('services.internal_notifications.email'),
            'klant' => $dossier->user?->email,
            default => null,
        };

        if (! $recipientEmail) {
            return;
        }

        Mail::to($recipientEmail)->send(new NewMessageMail(
            dossier: $dossier,
            messages: $messages,
            recipientRole: $recipientRole,
        ));

        $messages->each->update(['notified_at' => now()]);
        $notifier->markSent($this->dossierId, $this->authorRole);
    }
}
