<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('expiring-docs:notify')->dailyAt('09:00');
Schedule::command('expiring-docs:notify')->dailyAt('11:00');
Schedule::command('expiring-docs:notify')->dailyAt('12:00');
Schedule::command('expiring-docs:notify')->dailyAt('21:15');
Schedule::command('expiring-docs:notify')->dailyAt('21:30');
Schedule::command('expiring-docs:notify')->dailyAt('23:10');
Schedule::command('expiring-docs:notify')->dailyAt('23:20');
