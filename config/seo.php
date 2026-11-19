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
        'title' => 'Auto invoeren in Spanje? Wij regelen uw Spaans kenteken',
        'description' => 'Wij zetten uw Nederlandse auto op Spaans kenteken, van papierwerk en BPM-teruggave tot de ITV. Voor Nederlanders aan de Costa del Sol.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-route meta titles and descriptions
    |--------------------------------------------------------------------------
    |
    | Keyed by the route's name. Dynamic pages (e.g. blog posts) instead pass
    | a `seo` prop from their controller, which overrides anything here.
    |
    | `h1` and `intro` are each page's hero copy: shared with Vue through the
    | `seo` page prop and printed in the server-rendered crawler HTML
    | (resources/views/seo/fallback.blade.php), so both always match.
    | `h1_accent` is the part of the h1 shown in the accent colour.
    | `updated` is the last content review date, used as <lastmod> in the sitemap.
    |
    */

    'routes' => [
        'home' => [
            'title' => 'Auto invoeren in Spanje: wij regelen alles, vaste prijs',
            'description' => 'Nederlandse auto invoeren in Spanje? Wij regelen export, BPM-teruggave, ITV en inschrijving bij de DGT. Vaste prijs vanaf €395. Bereken direct uw kosten.',
            'h1' => 'Uw Nederlandse auto invoeren in Spanje, zonder gedoe',
            'h1_accent' => 'invoeren in Spanje',
            'intro' => 'Voor Nederlanders aan de Costa del Sol. Wij regelen het hele traject om uw auto in Spanje in te voeren, van papierwerk tot ITV en BPM-teruggave. Vul uw Nederlandse kenteken in en zie direct wat het u netto kost, met de BPM-teruggave al verrekend.',
            // Shown instead of the intro on small screens, so the kenteken field is in view right away.
            'intro_short' => 'Vul uw Nederlandse kenteken in en zie direct wat het u netto kost.',
            'updated' => '2026-11-19',
        ],

        'gids' => [
            'title' => 'Auto op Spaans kenteken zetten: stappenplan en kosten 2026',
            'description' => 'Uw Nederlandse auto op Spaans kenteken zetten? Alle stappen van RDW-export tot DGT, de documenten, de kosten en de ITV-valkuilen. Bijgewerkt 2026.',
            'h1' => 'Nederlandse auto op Spaans kenteken zetten: zo werkt het',
            'h1_accent' => 'op Spaans kenteken zetten',
            'intro' => 'Woont u in Spanje, dan moet uw Nederlandse auto op een Spaans kenteken. Hieronder staan alle stappen in de juiste volgorde, met de documenten en de kosten.',
            'published' => '2026-10-25',
            'updated' => '2026-11-19',
            'updated_label' => '19 november 2026',
        ],

        'diensten' => [
            'title' => 'Diensten: export, BPM-teruggave, ITV en Spaans kenteken',
            'description' => 'Wij regelen de RDW-export, BPM-teruggave, ITV-afspraak en inschrijving bij de DGT, in het Nederlands. Kies Basis, Compleet of Compleet Plus.',
            'h1' => 'Eén dossier, van RDW tot Spaans kenteken',
            'h1_accent' => 'RDW tot Spaans kenteken',
            'intro' => 'Wij begeleiden het volledige traject om uw Nederlandse auto op Spaans kenteken te zetten: de export en BPM-teruggave in Nederland, de keuring en inschrijving in Spanje.',
            'updated' => '2026-11-19',
        ],

        'over-ons' => [
            'title' => 'Over ons: Nederlandse autospecialist aan de Costa del Sol',
            'description' => 'Maikel (HBO Automotive, IVA) en Sebastiaan regelen uw auto op Spaans kenteken: in het Nederlands, tegen vaste prijs, vanuit de Costa del Sol.',
            'h1' => 'Over ons',
            'intro' => 'autoinvoerenspanje.nl is een samenwerking tussen een IVA-gecertificeerde autospecialist en een online marketeer. Samen begeleiden wij u door het hele importtraject, in het Nederlands en tegen een vaste prijs.',
            'updated' => '2026-11-19',
        ],

        // Feedbackpagina voor verloren leads; noindex (zie app.blade.php).
        'feedback.show' => [
            'title' => 'Uw feedback — autoinvoerenspanje.nl',
            'description' => 'Laat ons weten waarom u niet voor ons heeft gekozen, zodat wij onze service kunnen verbeteren.',
        ],

        // Link-in-bio page; noindex and not in the sitemap (see app.blade.php).
        'instagram' => [
            'title' => 'Auto op Spaans kenteken — autoinvoerenspanje.nl',
            'description' => 'Bereken direct wat het kost om uw Nederlandse auto op Spaans kenteken te zetten, of lees onze artikelen over BPM-teruggave, kosten en de ITV.',
        ],

        'contact' => [
            'title' => 'Contact — autoinvoerenspanje.nl',
            'description' => 'Neem contact op met ons team. Bel, WhatsApp of stuur een bericht via het contactformulier.',
            'h1' => 'Neem contact op',
            'intro' => 'Heeft u een vraag of wilt u een vrijblijvende offerte? Wij zijn bereikbaar via telefoon, WhatsApp of e-mail.',
            'updated' => '2026-10-25',
        ],

        'tarieven' => [
            'title' => 'Kosten auto invoeren in Spanje 2026: vaste prijs vanaf €395',
            'description' => 'Wat kost auto invoeren in Spanje? Drie vaste pakketten vanaf €395 plus alle Spaanse kosten: leges, gestoría, platen, ITV en registratiebelasting (IEDMT).',
            'h1' => 'Wat kost het om uw auto in Spanje in te voeren?',
            'h1_accent' => 'in Spanje in te voeren',
            'intro' => 'Onze begeleiding kost een vaste prijs. Daarbovenop komen de Spaanse kosten, die wij hieronder per post op een rij zetten, en daar staat uw BPM-teruggave tegenover.',
            'updated' => '2026-11-19',
        ],

        'bpm-calculator' => [
            'title' => 'Rest-BPM berekenen bij export — gratis BPM-teruggave calculator',
            'description' => 'Bereken gratis uw rest-BPM bij export. Vul uw Nederlandse kenteken in en zie direct hoeveel BPM u terugkrijgt, met de tabel van de Belastingdienst.',
            'h1' => 'Bereken uw rest-BPM bij export',
            'h1_accent' => 'BPM',
            'intro' => 'Voer uw Nederlandse kenteken in en zie gratis hoeveel BPM u ongeveer terugkrijgt bij export naar Spanje. Wij rekenen met de BPM die de RDW voor uw auto heeft geregistreerd en de vaste afschrijvingstabel van de Belastingdienst.',
            'updated' => '2026-10-29',
        ],

        'faq' => [
            'title' => 'Veelgestelde vragen: BPM, ITV, IEDMT en Spaans kenteken',
            'description' => 'Antwoorden over BPM-teruggave, ITV-keuring, registratiebelasting (IEDMT), documenten, doorlooptijd en kosten als u uw auto op Spaans kenteken zet.',
            'h1' => 'Veelgestelde vragen',
            'h1_accent' => 'vragen',
            'intro' => 'Antwoorden op de meest gestelde vragen over het importeren van een auto naar Spanje: van BPM-teruggave en de ITV-keuring tot documenten en kosten.',
            'updated' => '2026-11-19',
        ],

        'blog.index' => [
            'title' => 'Blog: export, BPM, ITV en uw Spaanse kenteken',
            'description' => 'Praktische artikelen over auto-import van Nederland naar Spanje, BPM-teruggave, IEDMT en alles wat erbij komt kijken.',
            'h1' => 'Praktische gidsen voor uw Spaanse import',
            'intro' => 'Stappenplannen, fiscale toelichting en ervaring uit de praktijk, geschreven voor Nederlanders die hun auto naar Spanje verhuizen.',
        ],
    ],
];
