<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

protected function schedule(Schedule $schedule)
{
   $schedule->command('expiring-docs:notify')->dailyAt('09:00');
    $schedule->command('expiring-docs:notify')->dailyAt('11:00');
    $schedule->command('expiring-docs:notify')->dailyAt('12:00');
       $schedule->command('expiring-docs:notify')->dailyAt('09:00');
    $schedule->command('expiring-docs:notify')->dailyAt('21:15');
    $schedule->command('expiring-docs:notify')->dailyAt('21:30');

    }
