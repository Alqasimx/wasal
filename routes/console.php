<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('wasal:sync-rent-dues')->dailyAt('00:15');
Schedule::command('wasal:notify-due-rent-payments')->dailyAt('08:00');
Schedule::command('wasal:sync-property-service-schedules')->hourly();
Schedule::command('wasal:notify-due-tasks')->hourly();
