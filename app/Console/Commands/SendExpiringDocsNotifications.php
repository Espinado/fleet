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
    protected $description = 'Send notification about documents expiring in <= 30 days (per company)';

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
        $companies = config('companies', []);

        // Группировка по фирме
        $grouped = $items->groupBy(fn ($it) => (int)($it->company_id ?? 0));

        $this->info("Найдено {$items->count()} документов. Компаний: {$grouped->count()}");
        Log::info("[Cron] Found {$items->count()} expiring documents on {$today}. Companies: {$grouped->count()}");

        // Всё без company_id — в лог (по желанию можно сделать отдельное письмо админу)
        if ($grouped->get(0, collect())->isNotEmpty()) {
            $cnt = $grouped->get(0)->count();
            Log::warning("[Cron] Found unassigned items with company_id=0, count={$cnt}");
        }

        foreach ($grouped as $companyId => $companyItems) {

            if ((int)$companyId === 0) {
                // неизвестная фирма — пропускаем
                continue;
            }

            $company = $companies[$companyId] ?? null;
            $email = $company['email'] ?? null;
            $companyName = $company['name'] ?? "Company #{$companyId}";

            if (!$email) {
                Log::warning("[Cron] Company {$companyId} ({$companyName}) has no email in config. Skipping.");
                continue;
            }

            try {
                Mail::to($email)->send(new ExpiringDocumentsReport($companyItems, $company));

                Log::info("[Cron] Sent expiring docs email to {$email} for company {$companyId} ({$companyName}), count={$companyItems->count()}");
                $this->info("✅ {$companyName}: отправлено на {$email} ({$companyItems->count()} шт.)");

            } catch (\Throwable $e) {
                Log::error("[Cron] Mail send failed for company {$companyId} ({$companyName}): " . $e->getMessage());
                $this->error("❌ {$companyName}: ошибка отправки на {$email}: " . $e->getMessage());
            }
        }

        Log::info('[Cron] Finished SendExpiringDocsNotifications at ' . now());
        $this->info("Уведомления обработаны.");
    }
}
