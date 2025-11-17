<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Тестовая метка — должна появляться в логе КАЖДУЮ минуту
        \Log::info('⚡ schedule() called at: ' . now());

        $schedule->command('expiring-docs:notify')->everyMinute();

        \Log::info('🚀 schedule() finished at: ' . now());
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        parent::commands();
    }
}
