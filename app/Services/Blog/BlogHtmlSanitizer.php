<?php

declare(strict_types=1);

namespace App\Services\Blog;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Allowlist for blog article HTML. Posts edited in the admin store raw HTML
 * that is rendered with v-html, so scripts, event handlers and javascript:
 * links are stripped before the HTML reaches a visitor.
 */
final class BlogHtmlSanitizer
{
    private readonly HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $this->sanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowMediaSchemes(['http', 'https'])
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->withMaxInputLength(500_000),
        );
    }

    public function sanitize(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}
