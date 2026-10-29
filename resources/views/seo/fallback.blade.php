{{--
    Server-rendered page content inside #app.

    The host can't run Node, so there is no Inertia SSR. Instead this partial
    prints each public page's core content as plain HTML, so crawlers and AI
    agents that don't execute JavaScript still get a heading, the copy, the
    FAQ and internal links. Vue mounts without hydration (no
    data-server-rendered attribute) and replaces this markup on load.

    The header and the homepage hero copy PublicLayout.vue and Welcome.vue
    class for class: the first paint is then already the real design, and the
    hero paragraph (the mobile LCP element) is painted from HTML instead of
    after all JavaScript has loaded. Keep them in sync when those change.

    All copy comes from the same sources the Vue pages use: config/seo.php,
    config/faq.php, resources/views/content/*, the database and page props.
--}}
@php
    $props = $page['props'] ?? [];
    $component = $page['component'] ?? '';
    $routeName = request()->route()?->getName();
    $meta = config('seo.routes', [])[$routeName] ?? [];
    // Non-breaking space, so an amount never wraps away from its € sign.
    $euro = fn (float $amount): string => "€\u{00A0}".number_format($amount, fmod($amount, 1) ? 2 : 0, ',', '.');

    // Same items as navItems in PublicLayout.vue.
    $nav = [
        '/diensten' => 'Diensten',
        '/tarieven' => 'Tarieven',
        '/bpm-calculator' => 'BPM-calculator',
        '/blog' => 'Blog',
        '/over-ons' => 'Over ons',
        '/contact' => 'Contact',
    ];
    // Linked from the footer, like the PublicLayout.vue footer does.
    $footerNav = [
        '/auto-invoeren-spanje' => 'Gids auto invoeren in Spanje',
        '/veelgestelde-vragen' => 'Veelgestelde vragen',
    ];
    $isHome = $component === 'Welcome';
    $check = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 stroke-[3] text-success" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';

    $posts = $props['posts'] ?? null;
    $postItems = match (true) {
        is_array($posts) => $posts['data'] ?? [],
        is_object($posts) && method_exists($posts, 'items') => $posts->items(),
        default => [],
    };

    $faqGroups = [];
    if (! empty($props['categories'])) {
        $faqGroups = $props['categories'];
    } elseif (! empty($props['faqItems'])) {
        $faqGroups = [['id' => 'faq', 'title' => 'Veelgestelde vragen', 'items' => $props['faqItems']]];
    }
@endphp

<div class="flex min-h-screen flex-col bg-background text-foreground">
    <header class="sticky top-0 z-40 border-b border-border bg-background/90 backdrop-blur">
        <div class="container mx-auto flex h-16 items-center justify-between px-4">
            <a href="/" class="-my-2 py-2 font-display text-xl font-semibold tracking-tight text-primary">autoinvoeren<span class="text-accent">spanje</span>.nl</a>
            <nav aria-label="Hoofdmenu" class="hidden items-center gap-7 text-sm font-medium text-foreground/80 lg:flex">
                @foreach ($nav as $href => $label)
                    <a href="{{ $href }}" class="relative inline-flex min-h-11 items-center">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-1.5 sm:gap-3">
                <a href="/login" class="hidden min-h-11 items-center text-sm font-medium text-foreground sm:inline-flex">Inloggen</a>
                <a href="/#offerte" class="hidden min-h-11 items-center rounded-xl bg-accent px-4 text-sm font-semibold text-accent-foreground shadow-sm sm:inline-flex">Offerte aanvragen</a>
                {{-- Same footprint as the hamburger button, which needs JavaScript. --}}
                <span class="flex size-11 lg:hidden" aria-hidden="true"></span>
            </div>
        </div>
    </header>

    @if ($isHome)
        <main class="flex-1">
            <section class="relative overflow-hidden bg-background">
                <div class="container mx-auto max-w-6xl px-4 py-16 text-center md:py-24">
                    <h1 class="font-display text-4xl leading-tight font-semibold text-balance text-foreground md:text-6xl">{!! str_replace('Spaans kenteken', '<span class="text-accent">Spaans kenteken</span>', e($meta['h1'] ?? config('seo.default.title'))) !!}</h1>
                    <p class="mx-auto mt-5 max-w-2xl text-lg text-pretty text-muted-foreground">{{ $meta['intro'] ?? '' }}</p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-muted-foreground">
                        @foreach (['Vaste prijs vooraf', 'Nederlandstalige begeleiding', 'BPM-teruggave inbegrepen'] as $usp)
                            <span class="inline-flex items-center gap-2">{!! $check !!} {{ $usp }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            <div class="container mx-auto max-w-3xl px-4 pb-16">
    @endif

    @unless ($isHome)
        <main class="container mx-auto max-w-3xl flex-1 px-4 py-16">
    @endunless
        @if ($component === 'Blog/Show' && ! empty($props['post']))
            <article class="prose prose-lg max-w-[68ch]">
                <p><a href="/blog">Alle artikelen</a></p>
                <h1>{{ trim($props['post']['title']) }}</h1>
                @if (! empty($props['post']['excerpt']))
                    <p>{{ $props['post']['excerpt'] }}</p>
                @endif
                @if (! empty($props['post']['author']))
                    <p>Door {{ $props['post']['author'] }}</p>
                @endif
                {!! $props['post']['content_html'] ?? '' !!}
            </article>
        @else
            @unless ($isHome)
                <h1 class="font-display text-4xl font-semibold">{{ $meta['h1'] ?? config('seo.default.title') }}</h1>
                @if (! empty($meta['intro']))
                    <p class="mt-5 text-lg text-muted-foreground">{{ $meta['intro'] }}</p>
                @endif
            @endunless

            @if ($component === 'Blog/Index')
                <ul class="mt-10 space-y-6">
                    @foreach ($postItems as $post)
                        <li>
                            <h2 class="font-display text-xl font-semibold"><a href="/blog/{{ $post['slug'] }}">{{ trim($post['title']) }}</a></h2>
                            @if (! empty($post['excerpt']))
                                <p>{{ $post['excerpt'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($component === 'Tarieven')
                <h2 class="mt-12 font-display text-2xl font-semibold">Pakketten</h2>
                <ul class="mt-4 space-y-4">
                    @foreach ($props['packages'] ?? [] as $package)
                        <li>
                            <strong>{{ $package['name'] }}: {{ $euro($package['price_eur']) }}</strong>, {{ $package['tagline'] }}.
                            <ul>
                                @foreach ($package['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
                <h2 class="mt-12 font-display text-2xl font-semibold">Vaste kosten in Spanje</h2>
                <ul class="mt-4">
                    @foreach ($props['fixedCosts'] ?? [] as $cost)
                        <li>{{ $cost['label'] }}: {{ $euro($cost['amount_eur']) }}</li>
                    @endforeach
                    <li>Registratiebelasting (IEDMT): 0% tot 120 g/km CO₂, daarboven 4,75% tot 14,75% van de waarde. Vrijstelling mogelijk bij verhuizing van uw hoofdverblijf.</li>
                </ul>
            @endif

            @if (! empty($props['contentHtml']))
                <div class="prose prose-lg mt-12 max-w-[68ch]">
                    {!! $props['contentHtml'] !!}
                </div>
            @endif
        @endif

        @foreach ($faqGroups as $group)
            <section class="mt-12">
                <h2 class="font-display text-2xl font-semibold">{{ $group['title'] }}</h2>
                <dl class="mt-4 space-y-6">
                    @foreach ($group['items'] as $item)
                        <div id="{{ $item['id'] }}">
                            <dt class="font-medium">{{ $item['question'] }}</dt>
                            <dd class="mt-1">{{ $item['answer'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach

        <p class="mt-12"><a href="/#offerte">Bereken wat het voor uw auto kost en vraag een offerte aan</a></p>
    @if ($isHome)
            </div>
    @endif
    </main>

    <footer class="border-t border-border">
        <div class="container mx-auto px-4 py-8 text-sm">
            <ul class="mb-4 flex flex-wrap gap-x-6 gap-y-2">
                @foreach ($footerNav as $href => $label)
                    <li><a href="{{ $href }}">{{ $label }}</a></li>
                @endforeach
            </ul>
            <p>autoinvoerenspanje.nl, voor Nederlanders aan de Costa del Sol die hun auto op Spaans kenteken willen zetten.</p>
            <p class="mt-2">
                Telefoon en WhatsApp: <a href="tel:+31618969732">+31 6 18 96 97 32</a> ·
                E-mail: <a href="mailto:info@autoinvoerenspanje.nl">info@autoinvoerenspanje.nl</a> · Málaga, Spanje
            </p>
        </div>
    </footer>
</div>
