<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Drain Redis metric buckets → metric_daily (Phase 7).
// Per-minute cadence balances near-real-time dashboards against lock contention.
Schedule::command('metrics:flush')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();
