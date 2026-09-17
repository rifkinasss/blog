<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('scheduler:publish-due')->everyMinute();
Schedule::command('operations:backup')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('backup:clean')->dailyAt('03:15')->withoutOverlapping();
