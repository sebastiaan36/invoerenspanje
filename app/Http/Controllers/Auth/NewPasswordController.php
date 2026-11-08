<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Services\Auth\FallbackPasswordBroker;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Http\Controllers\NewPasswordController as FortifyNewPasswordController;

/**
 * Fortify's reset controller, but the form also accepts tokens from the
 * "welcome" broker (the 7-day set-password link in the welcome mail).
 * Bound over Fortify's controller in FortifyServiceProvider.
 */
final class NewPasswordController extends FortifyNewPasswordController
{
    public const string WELCOME_BROKER = 'welcome';

    protected function broker(): PasswordBroker
    {
        return new FallbackPasswordBroker(
            Password::broker(config('fortify.passwords')),
            [Password::broker(self::WELCOME_BROKER)],
        );
    }
}
