<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:check-expired-tasks')
    ->daily()
    ->withoutOverlapping();

Schedule::command('app:generate-report')
    ->weeklyOn(1, '9:00')
    ->withoutOverlapping();
