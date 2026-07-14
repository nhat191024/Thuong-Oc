<?php

use App\Jobs\CleanupIdleTableJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CleanupIdleTableJob)->everyFifteenMinutes();
Schedule::command('reports:refresh-dish-sales')
    ->dailyAt('01:00')
    ->withoutOverlapping();
