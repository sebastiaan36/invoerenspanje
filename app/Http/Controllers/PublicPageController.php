<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Public marketing pages whose copy lives in Blade partials or config, so the
 * same content feeds both the Vue page and the server-rendered crawler HTML.
 */
final class PublicPageController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'faqItems' => config('faq.home'),
            'contentHtml' => view('content.home')->render(),
        ]);
    }

    public function tarieven(): Response
    {
        return Inertia::render('Tarieven', [
            'fixedCosts' => config('spain_import.fixed_costs'),
            'faqItems' => config('faq.tarieven'),
        ]);
    }

    public function bpmCalculator(): Response
    {
        return Inertia::render('BpmCalculator', [
            'contentHtml' => view('content.bpm-calculator')->render(),
        ]);
    }

    public function faq(): Response
    {
        return Inertia::render('Faq', [
            'categories' => config('faq.categories'),
        ]);
    }

    public function gids(): Response
    {
        return Inertia::render('Gids', [
            'contentHtml' => view('content.gids')->render(),
            'faqItems' => config('faq.home'),
            'updatedLabel' => 'Bijgewerkt: '.config('seo.routes.gids.updated_label'),
        ]);
    }
}
