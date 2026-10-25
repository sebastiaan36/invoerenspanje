<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($staticPages as $page)
    <url>
        <loc>{{ $page['url'] }}</loc>
        @if ($page['lastmod'])
        <lastmod>{{ $page['lastmod'] }}</lastmod>
        @endif
    </url>
    @endforeach
    @foreach ($posts as $post)
    <url>
        <loc>{{ url('/blog/' . $post->slug) }}</loc>
        <lastmod>{{ ($post->updated_at ?? $post->published_at)->toAtomString() }}</lastmod>
    </url>
    @endforeach
</urlset>
