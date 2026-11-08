<?php

declare(strict_types=1);

namespace App\Services\Mail;

/**
 * Makes free text safe to print inside a Markdown mail template.
 *
 * Blade's {{ }} only escapes HTML; the Markdown parser runs afterwards, so
 * input like "[klik](https://evil.example)" would become a real link.
 * Backslash-escaping the Markdown punctuation renders it as plain text.
 * HTML characters (&, <, >, ") are left to Blade's own escaping.
 */
final class MarkdownEscaper
{
    private const string SPECIAL_CHARACTERS = '\\`*_{}[]()#+-.!|~';

    public static function escape(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        return addcslashes($text, self::SPECIAL_CHARACTERS);
    }
}
