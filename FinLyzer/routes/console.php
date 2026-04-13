<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if ((bool) config('services.fintrack_feed.auto_schedule_enabled', false)) {
    Schedule::command('fintrack:auto-analyze')
        ->cron((string) config('services.fintrack_feed.auto_schedule_cron', '*/5 * * * *'))
        ->withoutOverlapping(10);
}
