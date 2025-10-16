<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\ExpiringDocumentsTable;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExpiringDocumentsReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // ✅ добавляем Log


class SendExpiringDocsNotifications extends Command
{
    protected $signature = 'expiring-docs:notify';
    protected $description = 'Send notification about documents expiring in <= 30 days';

    public function handle()
    {
        $component = n

        Log::info('🕒 [Cron] Starting SendExpiringDocsNotifications at ' . now());ew ExpiringDocumentsTable();
        $items = $component->collectItems();

        if ($items->isEmpty()) {
            $this->info('Нет документов, истекающих в ближайшие 30 дней.');
            return;
        }

        $today = Carbon::today()->toDateString();

        // Для теста — выводим в консоль
        $this->info("Найдено {$items->count()} документов с истекающим сроком:");
        foreach ($items->take(5) as $item) {
            $this->line("{$item->type}: {$item->name} — {$item->document} ({$item->expiry_date->toDateString()})");
        }

        // Отправляем письмо
        Mail::to('rvr@arguss.lv')->send(new ExpiringDocumentsReport($items));

        $this->info("Уведомление отправлено администратору.");
    }
}
