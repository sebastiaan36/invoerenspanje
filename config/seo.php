<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Site-wide SEO defaults
    |--------------------------------------------------------------------------
    |
    | These values are used for the Open Graph / social meta tags and as the
    | fallback title/description when a route has no specific entry below.
    |
    */

    'site_name' => 'autoinvoerenspanje.nl',

    'locale' => 'nl_NL',

    // Absolute or root-relative path to the default social sharing image.
    // Set to null to omit og:image / twitter:image when a page has none.
    'default_image' => '/og-image.jpg',

    'twitter_card' => 'summary_large_image',

    'default' => [
        'title' => 'Auto op Spaans kenteken — voor Nederlanders in Spanje',
        'description' => 'Wij regelen het hele traject om je Nederlandse auto op Spaans kenteken te zetten — van papierwerk en BPM-teruggave tot de ITV. Voor Nederlanders aan de Costa del Sol.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-route meta titles and descriptions
    |--------------------------------------------------------------------------
    |
    | Keyed by the route's name. Dynamic pages (e.g. blog posts) instead pass
    | a `seo` prop from their controller, which overrides anything here.
    |
    */

    'routes' => [
        'home' => [
            'title' => 'Auto op Spaans kenteken — voor Nederlanders in Spanje',
            'description' => 'Wij regelen het hele traject om je Nederlandse auto op Spaans kenteken te zetten — van papierwerk en BPM-teruggave tot de ITV. Voor Nederlanders aan de Costa del Sol.',
        ],

        'diensten' => [
            'title' => 'Diensten — Alles voor uw auto op Spaans kenteken',
            'description' => 'Van BPM-teruggave en auto-export tot ITV-begeleiding en volledige matriculación in Spanje. Ontdek onze diensten voor Nederlanders aan de Costa del Sol.',
        ],

        'over-ons' => [
            'title' => 'Over ons — autoinvoerenspanje.nl',
            'description' => 'Leer Maikel en Sebastiaan kennen — de samenwerking achter autoinvoerenspanje.nl. IVA-gecertificeerde autospecialist en online marketeer voor Nederlanders in Spanje.',
        ],

        'contact' => [
            'title' => 'Contact — autoinvoerenspanje.nl',
            'description' => 'Neem contact op met ons team. Bel, WhatsApp of stuur een bericht via het contactformulier.',
        ],

        'tarieven' => [
            'title' => 'Tarieven — Auto op Spaans kenteken',
            'description' => 'Transparante vaste prijzen voor het omzetten van uw Nederlandse auto naar Spaans kenteken. Bekijk onze drie pakketten en wat er bij inbegrepen is.',
        ],

        'bpm-calculator' => [
            'title' => 'BPM-calculator — Bereken uw BPM-teruggave',
            'description' => 'Voer uw kenteken in en zie direct hoeveel BPM-teruggave u kunt verwachten bij export van uw auto naar Spanje.',
        ],

        'faq' => [
            'title' => 'Veelgestelde vragen — Auto invoeren in Spanje',
            'description' => 'Antwoorden op alle vragen over het invoeren van uw auto in Spanje: BPM-teruggave, ITV-keuring, IEDMT, documenten, kosten en meer.',
        ],

        'blog.index' => [
            'title' => 'Blog — autoinvoerenspanje.nl',
            'description' => 'Praktische artikelen over auto-import van Nederland naar Spanje, BPM-teruggave, IEDMT en alles wat erbij komt kijken.',
        ],
    ],
];
