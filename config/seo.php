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
    | `h1` and `intro` feed the server-rendered crawler HTML (resources/views/
    | seo/fallback.blade.php) and mirror each page's hero. `updated` is the
    | last content review date, used as <lastmod> in the sitemap.
    |
    */

    'routes' => [
        'home' => [
            'title' => 'Auto op Spaans kenteken — voor Nederlanders in Spanje',
            'description' => 'Wij regelen het hele traject om uw Nederlandse auto op Spaans kenteken te zetten, van papierwerk en BPM-teruggave tot de ITV. Voor Nederlanders aan de Costa del Sol.',
            'h1' => 'Uw auto op Spaans kenteken, zonder gedoe.',
            'intro' => 'Voor Nederlanders aan de Costa del Sol. Wij regelen het hele traject, van papierwerk tot ITV en BPM-teruggave. Vul uw kenteken in en zie direct wat het u netto kost, met de BPM-teruggave al verrekend.',
            'updated' => '2026-10-25',
        ],

        'gids' => [
            'title' => 'Auto invoeren in Spanje: complete gids (2026)',
            'description' => 'Alle stappen om uw Nederlandse auto in Spanje in te voeren: export en BPM-teruggave, documenten, ITV-keuring, registratiebelasting (IEDMT) en een kostenoverzicht.',
            'h1' => 'Auto invoeren in Spanje: de complete gids',
            'intro' => 'Welke stappen er zijn, welke documenten u nodig heeft en wat het kost om uw Nederlandse auto op Spaans kenteken te zetten.',
            'updated' => '2026-10-25',
            'updated_label' => '25 oktober 2026',
        ],

        'diensten' => [
            'title' => 'Diensten — Alles voor uw auto op Spaans kenteken',
            'description' => 'Van BPM-teruggave en auto-export tot ITV-begeleiding en volledige matriculación in Spanje. Ontdek onze diensten voor Nederlanders aan de Costa del Sol.',
            'h1' => 'Eén dossier, van RDW tot Spaans kenteken',
            'intro' => 'Wij begeleiden het volledige traject om uw Nederlandse auto op Spaans kenteken te zetten: de export en BPM-teruggave in Nederland, de keuring en inschrijving in Spanje.',
            'updated' => '2026-10-25',
        ],

        'over-ons' => [
            'title' => 'Over ons — autoinvoerenspanje.nl',
            'description' => 'Maak kennis met Maikel en Sebastiaan, het team achter autoinvoerenspanje.nl: een autospecialist en uw online aanspreekpunt, voor Nederlanders in Spanje.',
            'h1' => 'Over ons',
            'intro' => 'autoinvoerenspanje.nl is een samenwerking tussen een IVA-gecertificeerde autospecialist en een online marketeer. Samen maken wij het importtraject eenvoudig, transparant en volledig Nederlandstalig.',
            'updated' => '2026-10-25',
        ],

        'contact' => [
            'title' => 'Contact — autoinvoerenspanje.nl',
            'description' => 'Neem contact op met ons team. Bel, WhatsApp of stuur een bericht via het contactformulier.',
            'h1' => 'Neem contact op',
            'intro' => 'Heeft u een vraag of wilt u een vrijblijvende offerte? Wij zijn bereikbaar via telefoon, WhatsApp of e-mail.',
            'updated' => '2026-10-25',
        ],

        'tarieven' => [
            'title' => 'Kosten auto invoeren in Spanje (2026) — vaste prijzen vanaf €395',
            'description' => 'Wat kost het om uw Nederlandse auto op Spaans kenteken te zetten? Drie vaste pakketten vanaf €395 plus alle Spaanse kosten op een rij: leges, platen, gestoría, ITV en IEDMT.',
            'h1' => 'Wat kost het om uw auto op Spaans kenteken te zetten?',
            'intro' => 'Een vaste prijs voor onze begeleiding, plus de vaste kosten in Spanje die wij hieronder voor u op een rij zetten. Geen verrassingen achteraf.',
            'updated' => '2026-10-25',
        ],

        'bpm-calculator' => [
            'title' => 'BPM-calculator — Bereken uw BPM-teruggave',
            'description' => 'Voer uw kenteken in en zie direct hoeveel BPM-teruggave u kunt verwachten bij export van uw auto naar Spanje.',
            'h1' => 'Hoeveel BPM krijgt u terug?',
            'intro' => 'Voer uw kenteken in en zie gratis hoeveel BPM u ongeveer terugkrijgt bij export naar Spanje. Wij rekenen met de BPM die de RDW voor uw auto heeft geregistreerd en de vaste afschrijvingstabel van de Belastingdienst.',
            'updated' => '2026-10-25',
        ],

        'faq' => [
            'title' => 'Veelgestelde vragen — Auto invoeren in Spanje',
            'description' => 'Antwoorden op alle vragen over het invoeren van uw auto in Spanje: BPM-teruggave, ITV-keuring, IEDMT, documenten, kosten en meer.',
            'h1' => 'Veelgestelde vragen',
            'intro' => 'Antwoorden op de meest gestelde vragen over het importeren van een auto naar Spanje: van BPM-teruggave en de ITV-keuring tot documenten en kosten.',
            'updated' => '2026-10-25',
        ],

        'blog.index' => [
            'title' => 'Blog — autoinvoerenspanje.nl',
            'description' => 'Praktische artikelen over auto-import van Nederland naar Spanje, BPM-teruggave, IEDMT en alles wat erbij komt kijken.',
            'h1' => 'Praktische gidsen voor uw Spaanse import',
            'intro' => 'Stappenplannen, fiscale toelichting en ervaring uit de praktijk, geschreven voor Nederlanders die hun auto naar Spanje verhuizen.',
        ],
    ],
];
