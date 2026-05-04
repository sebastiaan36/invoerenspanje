<?php

declare(strict_types=1);

namespace App\Services\Rdw;

final class KentekenNormalizer
{
    /**
     * Strip whitespace and dashes, uppercase the result.
     * Returns the bare RDW-friendly form (e.g. "12-ABC-3" → "12ABC3").
     */
    public static function normalize(string $input): string
    {
        return strtoupper(preg_replace('/[\s\-]+/', '', $input) ?? '');
    }

    /**
     * Permissive format check for Dutch kentekens.
     * Modern sidecodes are 6 alphanumeric characters; we allow 5–8 to cover edge cases
     * (special vehicles, historic plates) and let the RDW be the source of truth.
     */
    public static function isValidFormat(string $input): bool
    {
        $normalized = self::normalize($input);

        return preg_match('/^[A-Z0-9]{5,8}$/', $normalized) === 1
            && preg_match('/[A-Z]/', $normalized) === 1
            && preg_match('/[0-9]/', $normalized) === 1;
    }
}
