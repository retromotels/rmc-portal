<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily: remind owners who registered but never finished their details.
// (Requires the server cron to run `php artisan schedule:run` every minute.)
Schedule::command('rmc:pending-reminders')->dailyAt('09:00');
