<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

if (app()->environment(['local', 'testing'])) {
    Route::inertia('styleguide', 'Styleguide')->name('styleguide');
}

require __DIR__.'/settings.php';
