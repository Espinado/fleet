<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(Schedule $schedule)
    {
        // Привязываем расписание из Kernel принудительно
        app(\App\Console\Kernel::class)->schedule($schedule);
    }
}