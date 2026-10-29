{{--
    JSON-LD @graph, rendered server-side in <head> so crawlers see it without
    JavaScript. Only confirmed facts: no street address, no ratings.
--}}
@php
    $base = rtrim(config('app.url'), '/');
    $routeName = request()->route()?->getName();
    $props = $page['props'] ?? [];
    $orgId = $base.'/#organization';
    $siteId = $base.'/#website';
    $people = [
        'Maikel' => ['id' => $base.'/over-ons#maikel', 'jobTitle' => 'Autospecialist', 'image' => '/images/maikel.jpeg'],
        'Sebastiaan' => ['id' => $base.'/over-ons#sebastiaan', 'jobTitle' => 'Online aanspreekpunt', 'image' => '/images/sebastiaan.jpeg'],
    ];
    $hasOgImage = file_exists(public_path('og-image.jpg'));

    $areaServed = [
        ['@type' => 'AdministrativeArea', 'name' => 'Provincia de Málaga'],
        ['@type' => 'Place', 'name' => 'Costa del Sol'],
    ];
    foreach (config('werkgebied.plaatsen', []) as $plaats) {
        $areaServed[] = ['@type' => 'City', 'name' => $plaats['naam']];
    }

    $graph = [
        array_filter([
            '@type' => 'ProfessionalService',
            '@id' => $orgId,
            'name' => config('seo.site_name'),
            'url' => $base.'/',
            'logo' => $base.'/images/logo.svg',
            'image' => $hasOgImage ? $base.'/og-image.jpg' : null,
            'description' => config('seo.default.description'),
            'telephone' => '+34650015942',
            'email' => 'info@autoinvoerenspanje.nl',
            'priceRange' => '€395 - €1.495',
            'knowsLanguage' => 'nl',
            'areaServed' => $areaServed,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'telephone' => '+34650015942',
                'email' => 'info@autoinvoerenspanje.nl',
                'availableLanguage' => 'nl',
            ],
            'founder' => array_map(fn ($person) => ['@id' => $person['id']], array_values($people)),
        ]),
        [
            '@type' => 'WebSite',
            '@id' => $siteId,
            'url' => $base.'/',
            'name' => config('seo.site_name'),
            'inLanguage' => 'nl-NL',
            'publisher' => ['@id' => $orgId],
        ],
    ];

    foreach ($people as $name => $person) {
        $graph[] = [
            '@type' => 'Person',
            '@id' => $person['id'],
            'name' => $name,
            'jobTitle' => $person['jobTitle'],
            'image' => $base.$person['image'],
            'url' => $base.'/over-ons',
            'worksFor' => ['@id' => $orgId],
        ];
    }

    $pageTypes = ['over-ons' => 'AboutPage', 'contact' => 'ContactPage', 'blog.index' => 'CollectionPage', 'faq' => 'FAQPage'];
    $graph[] = [
        // FAQPage is the honest page type for the FAQ; no Question/Answer
        // markup is added because Google retired FAQ rich results in May 2026.
        '@type' => $pageTypes[$routeName] ?? 'WebPage',
        '@id' => $canonicalUrl.'#webpage',
        'url' => $canonicalUrl,
        'name' => $seoTitle,
        'description' => $seoDescription,
        'inLanguage' => 'nl-NL',
        'isPartOf' => ['@id' => $siteId],
        'about' => ['@id' => $orgId],
    ];

    $crumbNames = [
        'diensten' => 'Diensten', 'tarieven' => 'Tarieven', 'bpm-calculator' => 'BPM-calculator',
        'over-ons' => 'Over ons', 'contact' => 'Contact', 'faq' => 'Veelgestelde vragen',
        'gids' => 'Auto invoeren in Spanje', 'blog.index' => 'Blog', 'blog.show' => 'Blog',
    ];
    if (isset($crumbNames[$routeName])) {
        $items = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base.'/']];
        if ($routeName === 'blog.show') {
            $items[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $base.'/blog'];
            $items[] = ['@type' => 'ListItem', 'position' => 3, 'name' => trim($props['post']['title'] ?? $seoTitle)];
        } else {
            $items[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $crumbNames[$routeName]];
        }
        $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }

    if ($routeName === 'tarieven' && ! empty($props['packages'])) {
        $graph[] = [
            '@type' => 'Service',
            '@id' => $base.'/tarieven#service',
            'name' => 'Nederlandse auto op Spaans kenteken zetten',
            'serviceType' => 'Auto-import en matriculación (Nederland naar Spanje)',
            'provider' => ['@id' => $orgId],
            'areaServed' => $areaServed[0],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Pakketten',
                'itemListElement' => array_map(fn ($package) => [
                    '@type' => 'Offer',
                    'name' => $package['name'],
                    'description' => $package['tagline'],
                    'price' => (string) $package['price_eur'],
                    'priceCurrency' => 'EUR',
                    'url' => $base.'/tarieven',
                    'seller' => ['@id' => $orgId],
                ], $props['packages']),
            ],
        ];
    }

    if ($routeName === 'gids') {
        $updated = config('seo.routes.gids.updated');
        $graph[] = [
            '@type' => 'Article',
            '@id' => $canonicalUrl.'#article',
            'mainEntityOfPage' => ['@id' => $canonicalUrl.'#webpage'],
            'headline' => config('seo.routes.gids.h1'),
            'description' => $seoDescription,
            'dateModified' => $updated,
            'inLanguage' => 'nl-NL',
            'author' => ['@id' => $orgId],
            'publisher' => ['@id' => $orgId],
        ];
    }

    if ($routeName === 'blog.show' && ! empty($props['post'])) {
        $post = $props['post'];
        $authorFirstName = strtok((string) ($post['author'] ?? ''), ' ');
        $graph[] = array_filter([
            '@type' => 'BlogPosting',
            '@id' => $canonicalUrl.'#article',
            'mainEntityOfPage' => ['@id' => $canonicalUrl.'#webpage'],
            'headline' => mb_substr(trim($post['title']), 0, 110),
            'description' => trim((string) ($post['excerpt'] ?? '')),
            'image' => $post['hero_image_url'] ?? null,
            'datePublished' => $post['published_at'] ?? null,
            'dateModified' => $post['updated_at'] ?? $post['published_at'] ?? null,
            'inLanguage' => 'nl-NL',
            'author' => isset($people[$authorFirstName]) ? ['@id' => $people[$authorFirstName]['id']] : ['@id' => $orgId],
            'publisher' => ['@id' => $orgId],
        ], fn ($value) => $value !== null && $value !== '');
    }

    $schema = ['@context' => 'https://schema.org', '@graph' => $graph];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
