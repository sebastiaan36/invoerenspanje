<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

/**
 * Same answer whether or not a reset link was sent (unknown e-mail address,
 * broker throttle), so the forgot-password form doesn't reveal which e-mail
 * addresses have an account.
 */
final class NeutralPasswordResetLinkResponse implements FailedPasswordResetLinkRequestResponse, SuccessfulPasswordResetLinkRequestResponse
{
    public const string MESSAGE = 'Als dit e-mailadres bij ons bekend is, ontvangt u binnen enkele minuten een e-mail met een link om uw wachtwoord opnieuw in te stellen.';

    public function toResponse($request): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => self::MESSAGE], 200)
            : back()->with('status', self::MESSAGE);
    }
}
