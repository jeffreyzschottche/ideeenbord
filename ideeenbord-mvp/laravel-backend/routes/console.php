<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- AI content bots (only run when explicitly enabled via AI_BOTS_ENABLED) ---
if (config('ai.bots.enabled')) {
    Schedule::command('ai:seed-ideas')
        ->dailyAt('08:00')
        ->withoutOverlapping();

    Schedule::command('ai:distribute-interactions')
        ->dailyAt('12:00')
        ->withoutOverlapping();
}
