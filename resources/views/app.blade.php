<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @production
            {{-- Google tag (gtag.js) — GA4 --}}
            <script async src="https://www.googletagmanager.com/gtag/js?id=G-MER8N0VEN2"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());

                gtag('config', 'G-MER8N0VEN2');
            </script>
        @endproduction

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
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
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
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
            $canonicalUrl = url()->current();
        @endphp

        {{-- Title is rendered through Inertia's head so client-side navigation can update it --}}
        <x-inertia::head>
            <title>{{ $seoTitle }}</title>
        </x-inertia::head>

        {{-- Server-rendered SEO + social meta (visible to crawlers without JavaScript) --}}
        <meta name="description" content="{{ $seoDescription }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">

        <meta property="og:type" content="website">
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
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
