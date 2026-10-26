<?php

declare(strict_types=1);

namespace App\Services\Rdw;

use RuntimeException;
use Throwable;

/**
 * The RDW could not be reached or answered with an error. Distinct from
 * "kenteken not registered", which is a normal (null / empty) result.
 */
final class RdwUnavailableException extends RuntimeException
{
    public static function fromPrevious(string $kind, Throwable $previous): self
    {
        return new self("RDW {$kind} lookup failed: {$previous->getMessage()}", 0, $previous);
    }
}
