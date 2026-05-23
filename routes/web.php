<?php

use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Portal\DocumentController;
use App\Http\Controllers\Portal\MessageController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::inertia('diensten', 'Diensten')->name('diensten');
Route::inertia('over-ons', 'OverOns')->name('over-ons');
Route::inertia('contact', 'Contact')->name('contact');
Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::inertia('tarieven', 'Tarieven')->name('tarieven');
Route::inertia('bpm-calculator', 'BpmCalculator')->name('bpm-calculator');
Route::inertia('veelgestelde-vragen', 'Faq')->name('faq');

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::post('api/lookup', LookupController::class)->name('api.lookup');

Route::post('api/leads', [LeadController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('api.leads.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Klantportaal
|--------------------------------------------------------------------------
|
| Een klant kan meerdere dossiers hebben. /portaal toont een lijst,
| alle detail-pagina's leven onder /portaal/dossiers/{dossier}/...
| De DocumentController + MessageController scopen elke action op
| de eigenaar van het dossier (zie ownership-check in controllers).
|
*/

Route::middleware(['auth', 'klant'])->prefix('portaal')->name('portaal.')->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');

    // Backward-compat met de oude singular routes — pakken eerstvolgende dossier en redirecten.
    Route::get('dossier', [PortalController::class, 'redirectToFirstDossier'])->name('dossier.legacy');
    Route::get('documenten', fn () => redirect()->route('portaal.dashboard'));
    Route::get('berichten', fn () => redirect()->route('portaal.dashboard'));

    Route::prefix('dossiers/{dossier}')->name('dossiers.')->group(function () {
        Route::get('/', [PortalController::class, 'showDossier'])->name('show');

        Route::get('documenten', [PortalController::class, 'documents'])->name('documents');
        Route::post('documenten', [DocumentController::class, 'store'])->name('documents.store');
        Route::delete('documenten/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('berichten', [PortalController::class, 'messages'])->name('messages');
        Route::post('berichten', [MessageController::class, 'store'])->name('messages.store');
    });
});

// Bijlage- en document-downloads mogen door admins én eigenaars (auth-only, ownership in controller).
Route::middleware('auth')->group(function () {
    Route::get('portaal/berichten/attachments/{attachment}/download', [MessageController::class, 'downloadAttachment'])
        ->name('portaal.messages.attachments.download');
    Route::get('portaal/documenten/{document}/download', [DocumentController::class, 'download'])
        ->name('portaal.documents.download');
});

if (app()->environment(['local', 'testing'])) {
    Route::inertia('styleguide', 'Styleguide')->name('styleguide');
}

require __DIR__.'/settings.php';
