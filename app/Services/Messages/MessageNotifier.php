<?php

declare(strict_types=1);

namespace App\Services\Messages;

use App\Jobs\SendNewMessageDigest;
use App\Models\DossierMessage;
use Illuminate\Support\Facades\Cache;

/**
 * Plan mailnotificaties voor nieuwe dossier-berichten met een throttle van max 1 mail
 * per uur per dossier+richting. Eerste bericht in een venster gaat direct uit; latere
 * berichten in datzelfde venster worden later in één digest mee verstuurd.
 */
final class MessageNotifier
{
    /** Throttle window — max één mail per dit interval per dossier+richting. */
    private const WINDOW_MINUTES = 60;

    public function onCreated(DossierMessage $message): void
    {
        $direction = $this->recipientRoleFor($message->author_role);
        if ($direction === null) {
            return;
        }

        $sentKey = $this->sentKey($message->dossier_id, $direction);
        $deferredKey = $this->deferredKey($message->dossier_id, $direction);

        if (Cache::add($sentKey, true, now()->addMinutes(self::WINDOW_MINUTES))) {
            // Eerste bericht in het venster — direct dispatchen na DB-commit.
            SendNewMessageDigest::dispatch($message->dossier_id, $message->author_role)
                ->afterCommit();

            return;
        }

        // We zijn binnen het throttle-venster. Plan een digest aan het eind ervan,
        // tenzij er al een gepland staat (de tweede cache-key voorkomt dat).
        if (Cache::add($deferredKey, true, now()->addMinutes(self::WINDOW_MINUTES * 2))) {
            SendNewMessageDigest::dispatch($message->dossier_id, $message->author_role)
                ->delay(now()->addMinutes(self::WINDOW_MINUTES))
                ->afterCommit();
        }
    }

    /**
     * Markeer dat we voor dit venster een mail hebben verstuurd zodat de cache-state
     * en de berichten zelf consistent zijn. Aangeroepen door SendNewMessageDigest.
     */
    public function markSent(int $dossierId, string $authorRole): void
    {
        $direction = $this->recipientRoleFor($authorRole);
        if ($direction === null) {
            return;
        }

        // Reset de deferred-key zodat een volgend bericht meteen weer een directe mail krijgt.
        Cache::forget($this->deferredKey($dossierId, $direction));
    }

    public function recipientRoleFor(string $authorRole): ?string
    {
        return match ($authorRole) {
            'klant' => 'admin',
            'admin' => 'klant',
            default => null,
        };
    }

    private function sentKey(int $dossierId, string $direction): string
    {
        return "msg-mail-sent:{$dossierId}:{$direction}";
    }

    private function deferredKey(int $dossierId, string $direction): string
    {
        return "msg-mail-deferred:{$dossierId}:{$direction}";
    }
}
