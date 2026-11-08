<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Verloren leads 2 weken na 'verloren' om feedback vragen (eenmalig per lead).
Schedule::command('leads:request-feedback')
    ->dailyAt('10:00')
    ->timezone('Europe/Madrid')
    ->withoutOverlapping();
