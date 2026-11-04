<?php

declare(strict_types=1);

namespace App\Services\Blog;

use App\Models\Post;

/**
 * Turns links to blog posts that are not (yet) published into plain text.
 *
 * Posts are scheduled with a future `published_at`, so content may already
 * link to them. Until that moment the link would be a 404; this keeps the
 * anchor text and drops the link, so it becomes a link by itself once the
 * post goes live.
 */
final class UnpublishedBlogLinkFilter
{
    private const string LINK_PATTERN = '~<a\s[^>]*href="(?:https?://(?:www\.)?autoinvoerenspanje\.nl)?/blog/([a-z0-9-]+)/?"[^>]*>(.*?)</a>~is';

    public function filter(string $html): string
    {
        if (! str_contains($html, '/blog/')) {
            return $html;
        }

        // Looked up per call (not memoized): controllers holding this filter can
        // outlive a request, and a post may go live in between.
        $published = array_fill_keys(Post::published()->pluck('slug')->all(), true);

        return (string) preg_replace_callback(
            self::LINK_PATTERN,
            fn (array $match): string => isset($published[$match[1]]) ? $match[0] : $match[2],
            $html,
        );
    }
}
