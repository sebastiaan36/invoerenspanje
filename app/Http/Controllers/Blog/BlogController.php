<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = Post::published()
            ->with('author:id,name')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Post $p) => [
                'slug' => $p->slug,
                'title' => $p->title,
                'excerpt' => $p->excerpt,
                'hero_image_url' => $p->hero_image_path
                    ? asset('storage/'.$p->hero_image_path)
                    : null,
                'published_at' => $p->published_at?->toIso8601String(),
                'author' => $p->author?->name,
            ]);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(string $slug): Response
    {
        $post = Post::published()
            ->with('author:id,name')
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Blog/Show', [
            'post' => [
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'content_html' => $post->content_html,
                'hero_image_url' => $post->hero_image_path
                    ? asset('storage/'.$post->hero_image_path)
                    : null,
                'published_at' => $post->published_at?->toIso8601String(),
                'author' => $post->author?->name,
            ],
        ]);
    }
}
