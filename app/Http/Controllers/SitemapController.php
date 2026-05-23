<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $staticPages = [
            ['url' => url('/'),                  'priority' => '1.0',  'changefreq' => 'weekly'],
            ['url' => url('/diensten'),           'priority' => '0.9',  'changefreq' => 'monthly'],
            ['url' => url('/tarieven'),           'priority' => '0.9',  'changefreq' => 'monthly'],
            ['url' => url('/bpm-calculator'),     'priority' => '0.8',  'changefreq' => 'monthly'],
            ['url' => url('/over-ons'),           'priority' => '0.7',  'changefreq' => 'monthly'],
            ['url' => url('/contact'),            'priority' => '0.7',  'changefreq' => 'monthly'],
            ['url' => url('/blog'),              'priority' => '0.8',  'changefreq' => 'weekly'],
        ];

        $posts = Post::published()
            ->orderByDesc('published_at')
            ->get(['slug', 'published_at', 'updated_at']);

        return response(
            view('sitemap', compact('staticPages', 'posts'))->render(),
            200,
            ['Content-Type' => 'application/xml'],
        );
    }
}
