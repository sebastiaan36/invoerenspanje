<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repairs internal links in blog content that pointed at slugs which never
 * existed (found in the SEO audit of 25 Oct 2026), and strips trailing
 * slashes from internal links so they don't 301 on every click.
 */
return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private const REPLACEMENTS = [
        '/blog/exportkenteken-auto-uitschrijven-rdw' => '/blog/auto-uitschrijven-bij-de-rdw-en-exportkenteken-aanvragen-zo-werkt-het',
        '/blog/auto-invoeren-spanje-stappenplan' => '/auto-invoeren-spanje',
    ];

    public function up(): void
    {
        DB::table('posts')->select(['id', 'content_html'])->orderBy('id')->each(function (object $post): void {
            $html = (string) $post->content_html;
            $fixed = self::fixLinks($html);

            if ($fixed !== $html) {
                DB::table('posts')->where('id', $post->id)->update(['content_html' => $fixed]);
            }
        });
    }

    public function down(): void
    {
        // Content repair; the broken links are not worth restoring.
    }

    public static function fixLinks(string $html): string
    {
        // Internal hrefs (relative or on our own domain) without a trailing slash.
        $html = preg_replace(
            '#href="((?:https?://(?:www\.)?autoinvoerenspanje\.nl)?/[^"\#?]+?)/(["\#?])#i',
            'href="$1$2',
            $html,
        );

        foreach (self::REPLACEMENTS as $from => $to) {
            $html = preg_replace(
                '#href="((?:https?://(?:www\.)?autoinvoerenspanje\.nl)?)'.preg_quote($from, '#').'(["\#?])#i',
                'href="'.$to.'$2',
                $html,
            );
        }

        return $html;
    }
};
