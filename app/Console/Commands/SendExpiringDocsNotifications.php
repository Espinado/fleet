<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\ExpiringDocumentsTable;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExpiringDocumentsReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendExpiringDocsNotifications extends Command
{
    protected $signature = 'expiring-docs:notify';
    protected $description = 'Send notification about documents expiring in <= 30 days';

    public function handle()
    {
        Log::info('[Cron] Starting SendExpiringDocsNotifications at ' . now());

        $component = new ExpiringDocumentsTable();
        $items = $component->collectItems();

        if ($items->isEmpty()) {
            Log::info('[Cron] No expiring documents found.');
            $this->info('Нет документов, истекающих в ближайшие 30 дней.');
            return;
        }

        $today = Carbon::today()->toDateString();

        Log::info("[Cron] Found {$items->count()} expiring documents on {$today}.");
        $this->info("Найдено {$items->count()} документов с истекающим сроком:");
        foreach ($items->take(5) as $item) {
            $this->line("{$item->type}: {$item->name} — {$item->document} ({$item->expiry_date->toDateString()})");
        }

        try {
            Mail::to('rvr@arguss.lv')->send(new ExpiringDocumentsReport($items));
            Log::info('[Cron] Expiring documents email sent successfully.');
        } catch (\Throwable $e) {
            Log::error('[Cron] Mail send failed: ' . $e->getMessage());
            $this->error('Ошибка при отправке письма: ' . $e->getMessage());
        }

        Log::info('[Cron] Finished SendExpiringDocsNotifications at ' . now());
        $this->info("Уведомление обработано.");
    }
}
