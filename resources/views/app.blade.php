<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @production
            {{-- Google tag (gtag.js) — GA4. The 196 KB library loads after the page
                 has finished loading so it doesn't compete with the first render;
                 calls made before then queue up in dataLayer. --}}
            <script nonce="{{ Vite::cspNonce() }}">
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', 'G-MER8N0VEN2');

                window.addEventListener('load', function () {
                    var script = document.createElement('script');
                    script.async = true;
                    script.src = 'https://www.googletagmanager.com/gtag/js?id=G-MER8N0VEN2';
                    document.head.appendChild(script);
                });
            </script>
        @endproduction

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script nonce="{{ Vite::cspNonce() }}">
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            /* Matches --background from app.css, so overscroll and the first
               paint before the stylesheet loads use the page colour. */
            html {
                background-color: var(--background, hsl(40 36% 95%));
            }

            html.dark {
                background-color: var(--background, hsl(30 12% 9%));
            }
        </style>

        <meta name="google-site-verification" content="aic5gyu9qRZ59JSYartIsHVi4fXWVDPLDa7ky_ioSzU" />

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @php
            $seo = $page['props']['seo'] ?? [];
            $seoTitle = $seo['title'] ?? config('seo.default.title');
            $seoDescription = $seo['description'] ?? config('seo.default.description');
            $seoImage = $seo['image'] ?? null;

            // Built from APP_URL plus the route path, so aliases like /index.php
            // or a stray host never become their own canonical.
            $path = trim(request()->path(), '/');
            $canonicalUrl = rtrim(config('app.url'), '/').($path === '' ? '' : '/'.$path);

            $isPublicPage = in_array($page['component'], ['Welcome', 'Diensten', 'Tarieven', 'BpmCalculator', 'Contact', 'OverOns', 'Faq', 'Gids'], true)
                || str_starts_with($page['component'], 'Blog/');
            $isArticle = $page['component'] === 'Blog/Show';
        @endphp

        {{-- Title is rendered through Inertia's head so client-side navigation can update it --}}
        <x-inertia::head>
            <title>{{ $seoTitle }}</title>
        </x-inertia::head>

        {{-- Server-rendered SEO + social meta (visible to crawlers without JavaScript) --}}
        <meta name="description" content="{{ $seoDescription }}">
        @if ($isPublicPage)
            <link rel="canonical" href="{{ $canonicalUrl }}">
        @else
            <meta name="robots" content="noindex, nofollow">
        @endif

        <meta property="og:type" content="{{ $isArticle ? 'article' : 'website' }}">
        <meta property="og:site_name" content="{{ config('seo.site_name') }}">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:locale" content="{{ config('seo.locale') }}">
        @if ($seoImage)
            <meta property="og:image" content="{{ $seoImage }}">
        @endif

        <meta name="twitter:card" content="{{ config('seo.twitter_card') }}">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
        @if ($seoImage)
            <meta name="twitter:image" content="{{ $seoImage }}">
        @endif

        @if ($isPublicPage)
            @include('partials.schema')
        @endif
    </head>
    <body class="font-sans antialiased">
        {{-- Same output as <x-inertia::app />, but #app carries server-rendered
             content for crawlers on public pages (see seo/fallback). --}}
        <script data-page="app" type="application/json">{!! json_encode($page) !!}</script>
        <div id="app">
            @if ($isPublicPage)
                @include('seo.fallback')
            @endif
        </div>
    </body>
</html>
