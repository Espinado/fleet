<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Truck;
use App\Models\Trailer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ExpiringDocsNotifier
{
    public static function send()
    {
        $today = Carbon::today();
        $deadline = $today->copy()->addDays(30); // проверяем документы на 30 дней вперед
        $items = collect();

        // ---------- Drivers ----------
        Driver::all()->each(function($d) use ($items, $today, $deadline) {
            $docs = [
                'License' => $d->license_end,
                '95 Code' => $d->code95_end,
                'Permit' => $d->permit_expired,
                'Medical' => $d->medical_expired,
                'Declaration' => $d->declaration_expired,
            ];

            foreach ($docs as $docName => $dateVal) {
                if (!$dateVal) continue;
                $expiry = Carbon::parse($dateVal);
                if ($expiry->gt($deadline)) continue;
                $daysLeft = $today->diffInDays($expiry, false);
                $items->push([
                    'type' => 'Driver',
                    'name' => "{$d->first_name} {$d->last_name}",
                    'document' => $docName,
                    'expiry_date' => $expiry->toDateString(),
                    'days_left' => $daysLeft,
                ]);
            }
        });

        // ---------- Trucks ----------
        Truck::all()->each(function($t) use ($items, $today, $deadline) {
            $docs = [
                'Inspection' => $t->inspection_expired,
                'Insurance' => $t->insurance_expired,
                'Tech passport' => $t->tech_passport_expired,
            ];

            foreach ($docs as $docName => $dateVal) {
                if (!$dateVal) continue;
                $expiry = Carbon::parse($dateVal);
                if ($expiry->gt($deadline)) continue;
                $daysLeft = $today->diffInDays($expiry, false);
                $items->push([
                    'type' => 'Truck',
                    'name' => "{$t->brand} {$t->model} ({$t->plate})",
                    'document' => $docName,
                    'expiry_date' => $expiry->toDateString(),
                    'days_left' => $daysLeft,
                ]);
            }
        });

        // ---------- Trailers ----------
        Trailer::all()->each(function($tr) use ($items, $today, $deadline) {
            $docs = [
                'Inspection' => $tr->inspection_expired,
                'Insurance' => $tr->insurance_expired,
                'TIR' => $tr->tir_expired,
                'Tech passport' => $tr->tech_passport_expired,
            ];

            foreach ($docs as $docName => $dateVal) {
                if (!$dateVal) continue;
                $expiry = Carbon::parse($dateVal);
                if ($expiry->gt($deadline)) continue;
                $daysLeft = $today->diffInDays($expiry, false);
                $items->push([
                    'type' => 'Trailer',
                    'name' => "{$tr->brand} ({$tr->plate})",
                    'document' => $docName,
                    'expiry_date' => $expiry->toDateString(),
                    'days_left' => $daysLeft,
                ]);
            }
        });

        // ---------- Отправка Email ----------
        if ($items->isEmpty()) return;

        $emails = ['admin@example.com']; // Заменить на нужные адреса
        foreach ($emails as $email) {
            Mail::raw(self::formatMessage($items), function ($message) use ($email) {
                $message->to($email)
                        ->subject('Expiring Documents Notification');
            });
        }
    }

    protected static function formatMessage($items)
    {
        $lines = [];
        foreach ($items as $i) {
            $status = $i['days_left'] < 0
                ? abs($i['days_left']).' days overdue'
                : $i['days_left'].' days left';
            $lines[] = "{$i['type']}: {$i['name']} — {$i['document']} expires on {$i['expiry_date']} ({$status})";
        }
        return implode("\n", $lines);
    }
}
