<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Closure;
use Illuminate\Contracts\Auth\PasswordBroker;

/**
 * Lets one reset form accept tokens from several password brokers.
 *
 * Reset links are always sent through the primary broker. When resetting,
 * each broker is tried in turn while the token is unknown to the previous
 * one; any other outcome (success, unknown user) is returned immediately.
 * Each broker keeps its own token table and expiry.
 */
final class FallbackPasswordBroker implements PasswordBroker
{
    /**
     * @param  list<PasswordBroker>  $fallbacks
     */
    public function __construct(
        private readonly PasswordBroker $primary,
        private readonly array $fallbacks,
    ) {}

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function sendResetLink(array $credentials, ?Closure $callback = null): string
    {
        return $this->primary->sendResetLink($credentials, $callback);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function reset(array $credentials, Closure $callback): mixed
    {
        $status = $this->primary->reset($credentials, $callback);

        foreach ($this->fallbacks as $broker) {
            if ($status !== self::INVALID_TOKEN) {
                break;
            }

            $status = $broker->reset($credentials, $callback);
        }

        return $status;
    }
}
