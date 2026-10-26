<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers for every web response (also the Filament panel).
 *
 * The enforced CSP only holds directives that cannot break a page (base-uri,
 * object-src, frame-ancestors, form-action). The full script/style policy runs
 * in Report-Only mode with a per-request nonce (also applied to the Vite tags)
 * until the browser console shows no violations; then it can be enforced.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();

        // GA4 beacons, per Google's CSP guidance: ga-audiences goes to the
        // visitor's country domain (google.es / google.nl for this audience).
        $analyticsHosts = implode(' ', [
            'https://*.google-analytics.com',
            'https://*.analytics.google.com',
            'https://*.googletagmanager.com',
            'https://*.g.doubleclick.net',
            'https://*.google.com',
            'https://*.google.es',
            'https://*.google.nl',
        ]);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
        ]));
        $response->headers->set('Content-Security-Policy-Report-Only', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic' https:",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob: https://tile.openstreetmap.org {$analyticsHosts}",
            "connect-src 'self' {$analyticsHosts}",
            "font-src 'self' data:",
            "frame-src 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
