{{--
    Server-rendered page content inside #app.

    The host can't run Node, so there is no Inertia SSR. Instead this partial
    prints each public page's core content as plain HTML, so crawlers and AI
    agents that don't execute JavaScript still get a heading, the copy, the
    FAQ and internal links. Vue mounts without hydration (no
    data-server-rendered attribute) and replaces this markup on load.

    All copy comes from the same sources the Vue pages use: config/seo.php,
    config/faq.php, resources/views/content/*, the database and page props.
--}}
@php
    $props = $page['props'] ?? [];
    $component = $page['component'] ?? '';
    $routeName = request()->route()?->getName();
    $meta = config('seo.routes', [])[$routeName] ?? [];
    $euro = fn (float $amount): string => '€ '.number_format($amount, fmod($amount, 1) ? 2 : 0, ',', '.');

    $nav = [
        '/diensten' => 'Diensten',
        '/tarieven' => 'Tarieven',
        '/bpm-calculator' => 'BPM-calculator',
        '/auto-invoeren-spanje' => 'Gids auto invoeren in Spanje',
        '/blog' => 'Blog',
        '/veelgestelde-vragen' => 'Veelgestelde vragen',
        '/over-ons' => 'Over ons',
        '/contact' => 'Contact',
    ];

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
    <header class="border-b border-border">
        <div class="container mx-auto flex flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="/" class="font-display text-xl font-semibold text-primary">autoinvoerenspanje.nl</a>
            <nav aria-label="Hoofdmenu">
                <ul class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                    @foreach ($nav as $href => $label)
                        <li><a href="{{ $href }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto max-w-3xl flex-1 px-4 py-16">
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
            <h1 class="font-display text-4xl font-semibold">{{ $meta['h1'] ?? config('seo.default.title') }}</h1>
            @if (! empty($meta['intro']))
                <p class="mt-5 text-lg text-muted-foreground">{{ $meta['intro'] }}</p>
            @endif

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
    </main>

    <footer class="border-t border-border">
        <div class="container mx-auto px-4 py-8 text-sm">
            <p>autoinvoerenspanje.nl, voor Nederlanders aan de Costa del Sol die hun auto op Spaans kenteken willen zetten.</p>
            <p class="mt-2">
                Telefoon en WhatsApp: <a href="tel:+31618969732">+31 6 18 96 97 32</a> ·
                E-mail: <a href="mailto:info@autoinvoerenspanje.nl">info@autoinvoerenspanje.nl</a> · Málaga, Spanje
            </p>
        </div>
    </footer>
</div>
