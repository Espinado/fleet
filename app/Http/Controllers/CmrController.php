<?php

namespace App\Http\Controllers;

use App\Models\TripCargo;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Helpers\CalculateTax;

class CmrController extends Controller
{

    private function getOrCreateOrderNumber($trip, $cargos): string
{
    // 🟢 Проверяем — есть ли уже order_nr у одного из этих грузов
    $existingOrderNr = $cargos->firstWhere('order_nr', '!=', null)?->order_nr;

    if ($existingOrderNr) {
        return $existingOrderNr; // ✅ возвращаем существующий номер
    }

    // 🟢 Если нет — создаём новый номер:
    // Формат: [НОМЕР_МАШИНЫ_БЕЗ_ПРОБЕЛОВ]/[ДЕНЬ]
    $orderNr = str_replace(' ', '', $trip->truck->plate ?? 'NO_PLATE') . '/' . $trip->id;

    // 🟢 Сохраняем номер во все грузы этой пары
    foreach ($cargos as $c) {
        $c->update(['order_nr' => $orderNr]);
    }

    return $orderNr;
}
    private function numberToWordsLv($amount): string
{
    $units = [
        0 => 'nulle', 1 => 'viens', 2 => 'divi', 3 => 'trīs', 4 => 'četri', 5 => 'pieci',
        6 => 'seši', 7 => 'septiņi', 8 => 'astoņi', 9 => 'deviņi'
    ];

    $tens = [
        10 => 'desmit', 11 => 'vienpadsmit', 12 => 'divpadsmit', 13 => 'trīspadsmit', 14 => 'četrpadsmit',
        15 => 'piecpadsmit', 16 => 'sešpadsmit', 17 => 'septiņpadsmit', 18 => 'astoņpadsmit', 19 => 'deviņpadsmit',
        20 => 'divdesmit', 30 => 'trīsdesmit', 40 => 'četrdesmit', 50 => 'piecdesmit',
        60 => 'sešdesmit', 70 => 'septiņdesmit', 80 => 'astoņdesmit', 90 => 'deviņdesmit'
    ];

    $n = floor($amount);
    if ($n == 0) return 'Nulle EUR, 00 centi';

    $words = [];
    if ($n >= 1000) {
        $words[] = $units[intval($n / 1000)] . ' tūkstoši';
        $n %= 1000;
    }

    if ($n >= 100) {
        $words[] = $units[intval($n / 100)] . ' simti';
        $n %= 100;
    }

    if ($n >= 20) {
        $words[] = $tens[intval(floor($n / 10) * 10)];
        $n %= 10;
    }

    if ($n > 0) {
        $words[] = $units[$n];
    }

    return ucfirst(trim(implode(' ', $words))) . ' EUR, 00 centi';
}

    public function generateAndSave(TripCargo $cargo)
    {
        $trip = $cargo->trip;
       
        // 🟢 Находим все грузы для этой пары (shipper → consignee)
        $cargos = $trip->cargos()
            ->where('shipper_id', $cargo->shipper_id)
            ->where('consignee_id', $cargo->consignee_id)
            ->get();
             $cmr_Nr = $this->getOrCreateOrderNumber($trip, $cargos);


        if ($cargos->isEmpty()) {
            return back()->with('error', 'No cargos found for this pair.');
        }

        $shipper   = $cargos->first()->shipper;
        $consignee = $cargos->first()->consignee;

        // 🟢 Собираем все items из всех грузов
        $allItems = [];

        foreach ($cargos as $c) {
          foreach ($c->items as $item) {
  $allItems[] = [
    'cargo_paletes' => $item->cargo_paletes ?? 0,
    'packages'      => $item->packages ?? 0,
    'cargo_tonnes'  => $item->cargo_tonnes ?? 0,
    'desc'          => $item->description ?? '',
    'weight'        => $item->weight ?? 0,
    'volume'        => $item->volume ?? 0,
    'price_with_tax'=> $item->price_with_tax ?? 0, // 👈 важно
];
}
        }

        if (empty($allItems)) {
            return back()->with('error', 'No cargo items found for this client pair.');
        }

        // 🟢 Формируем все данные для PDF
        $data = [
            'sender' => [
                'name'     => $shipper->company_name ?? '—',
                'address'  => $shipper->fiz_address ?? $shipper->jur_address ?? '—',
                'city'     => getCityById(
                    (int)($shipper->fiz_city_id ?? $shipper->jur_city_id),
                    (int)($shipper->fiz_country_id ?? $shipper->jur_country_id)
                ),
                'country'  => getCountryById(
                    (int)($shipper->fiz_country_id ?? $shipper->jur_country_id)
                ),
                'reg_nr'   => $shipper->reg_nr ?? '—',
            ],

            'receiver' => [
                'name'     => $consignee->company_name ?? '—',
                'address'  => $consignee->fiz_address ?? $consignee->jur_address ?? '—',
                'city'     => getCityById(
                    (int)($consignee->fiz_city_id ?? $consignee->jur_city_id),
                    (int)($consignee->fiz_country_id ?? $consignee->jur_country_id)
                ),
                'country'  => getCountryById(
                    (int)($consignee->fiz_country_id ?? $consignee->jur_country_id)
                ),
                'reg_nr'   => $consignee->reg_nr ?? '—',
            ],

            'carrier' => [
                'name'           => $trip->expeditor_name ?? '—',
                'address'        => $trip->expeditor_address ?? '—',
                'city'           => $trip->expeditor_city ?? '—',
                'country'        => $trip->expeditor_country ?? '—',
                'reg_nr'         => $trip->expeditor_reg_nr ?? '—',
                'driver'         => trim(($trip->driver->first_name ?? '') . ' ' . ($trip->driver->last_name ?? '')) ?: '—',
                'truck'          => trim(($trip->truck->brand ?? '') . ' ' . ($trip->truck->model ?? '')) ?: '—',
                'truck_plate'    => $trip->truck->plate ?? '—',
                'trailer'        => trim(($trip->trailer->brand ?? '') . ' ' . ($trip->trailer->model ?? '')) ?: '—',
                'trailer_plate'  => $trip->trailer->plate ?? '—',
            ],

            'loading_place'     => getCityById((int)$cargo->loading_city_id, (int)$cargo->loading_country_id)
                                    . ', ' . getCountryById((int)$cargo->loading_country_id),
            'unloading_place'   => getCityById((int)$cargo->unloading_city_id, (int)$cargo->unloading_country_id)
                                    . ', ' . getCountryById((int)$cargo->unloading_country_id),
            'loading_address'   => $cargo->loading_address ?? '',
            'unloading_address' => $cargo->unloading_address ?? '',
            'items'             => $allItems,
            'date'              => Carbon::now()->format('d.m.Y'),
            'trip_id'           => $trip->id,
            'cmr_nr'            => $cmr_Nr,
        ];

        // 🟢 Подготовка путей
        $tripId    = $trip->id ?? 0;
        $dir       = "cmr/trip_{$tripId}";
        $fileName  = "cmr_{$cargo->shipper_id}_{$cargo->consignee_id}.pdf";
        $storagePath = "public/{$dir}/{$fileName}";
        $publicUrl = asset("storage/{$dir}/{$fileName}");

        Storage::disk('public')->makeDirectory($dir);

        // 🟢 Генерация PDF
        $pdf = Pdf::loadView('pdf.cmr-template', $data)
            ->setPaper('A4')
             ->setPaper('A4', 'portrait')
    ->setOption('margin-top', 0)
    ->setOption('margin-right', 0)
    ->setOption('margin-bottom', 0)
    ->setOption('margin-left', 0);

        // 🟢 Сохраняем PDF в storage
        Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

        // 🟢 Обновляем все грузы этой пары
        foreach ($cargos as $c) {
            $c->update([
                'cmr_file'       => "cmr/trip_{$tripId}/{$fileName}",
                'cmr_created_at' => now(),
                'cmr_nr'         => $cmr_Nr,
            ]);
        }

        // 🟢 Возвращаем ссылку для открытия
        return $publicUrl;
    }
public function generateTransportOrder(TripCargo $cargo)
{
    $trip = $cargo->trip;

    // 🟢 Грузы для пары shipper → consignee → customer
    $cargos = $trip->cargos()
        ->where('shipper_id', $cargo->shipper_id)
        ->where('consignee_id', $cargo->consignee_id)
        ->where('customer_id', $cargo->customer_id)
        ->get();

    if ($cargos->isEmpty()) {
        return back()->with('error', 'Nav atrasta neviena krava šim pārim.');
    }

    $shipper   = $cargos->first()->shipper;
    $consignee = $cargos->first()->consignee;
    $customer  = $cargos->first()->customer;

    // 🟢 Берём срок оплаты из первого найденного груза (или null)
    $paymentTerms = $cargos->firstWhere('payment_terms', '!=', null)?->payment_terms ?? null;

    $orderNr = $this->getOrCreateOrderNumber($trip, $cargos);
    $totalPriceWithTax = $cargos->sum('price_with_tax');

    $data = [
        'sender' => [
            'name'     => $shipper->company_name ?? '—',
            'cargo'    => $cargo,
            'address'  => $shipper->fiz_address ?? $shipper->jur_address ?? '—',
            'city'     => getCityById(
                (int)($shipper->fiz_city_id ?? $shipper->jur_city_id),
                (int)($shipper->fiz_country_id ?? $shipper->jur_country_id)
            ),
            'country'  => getCountryById(
                (int)($shipper->fiz_country_id ?? $shipper->jur_country_id)
            ),
            'reg_nr'   => $shipper->reg_nr ?? '—',
        ],

        'customer' => [
            'name'     => $customer->company_name ?? '—',
            'cargo'    => $cargo,
            'address'  => $customer->fiz_address ?? $customer->jur_address ?? '—',
            'city'     => getCityById(
                (int)($customer->fiz_city_id ?? $customer->jur_city_id),
                (int)($customer->fiz_country_id ?? $customer->jur_country_id)
            ),
            'country'  => getCountryById(
                (int)($customer->fiz_country_id ?? $customer->jur_country_id)
            ),
            'reg_nr'   => $customer->reg_nr ?? '—',
        ],

        'receiver'   => $consignee,
        'carrier'    => [
            'name'          => $trip->expeditor_name ?? '—',
            'address'       => $trip->expeditor_address ?? '—',
            'city'          => $trip->expeditor_city ?? '—',
            'country'       => $trip->expeditor_country ?? '—',
            'reg_nr'        => $trip->expeditor_reg_nr ?? '—',
            'driver'        => trim(($trip->driver->first_name ?? '') . ' ' . ($trip->driver->last_name ?? '')) ?: '—',
            'truck'         => trim(($trip->truck->brand ?? '') . ' ' . ($trip->truck->model ?? '')) ?: '—',
            'truck_plate'   => $trip->truck->plate ?? '—',
            'trailer'       => trim(($trip->trailer->brand ?? '') . ' ' . ($trip->trailer->model ?? '')) ?: '—',
            'trailer_plate' => $trip->trailer->plate ?? '—',
        ],

        'loading_place'     => getCityById($cargo->loading_city_id, $cargo->loading_country_id) . ', ' . getCountryById($cargo->loading_country_id),
        'unloading_place'   => getCityById($cargo->unloading_city_id, $cargo->unloading_country_id) . ', ' . getCountryById($cargo->unloading_country_id),
        'loading_address'   => $cargo->loading_address ?? '',
        'unloading_address' => $cargo->unloading_address ?? '',
        'date'              => now()->format('d.m.Y'),
        'trip'              => $trip,
        'order_nr'          => $orderNr,
        'payment_terms'     => $paymentTerms, // ✅ исправлено и добавлено корректно
        'total_price_with_tax' => $totalPriceWithTax,
    ];

    // 🗂️ Папка и имя файла
    $dir = "orders/order_{$trip->id}";
    $fileName = "transport_order.pdf";

    Storage::disk('public')->makeDirectory($dir);

    // 🧾 PDF
    $pdf = Pdf::loadView('pdf.transport-order', $data)
        ->setPaper('A4')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

    Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

    // 🔵 Обновляем все грузы этой пары
    foreach ($cargos as $c) {
        $c->update([
            'order_file'       => "{$dir}/{$fileName}",
            'order_created_at' => now(),
            'order_nr'         => $orderNr,
        ]);
    }

    return asset("storage/{$dir}/{$fileName}");
}
public function generateInvoice(TripCargo $cargo)
{
    $trip = $cargo->trip;

    // 🟢 Грузы для пары shipper → consignee → customer
    $cargos = $trip->cargos()
        ->where('shipper_id', $cargo->shipper_id)
        ->where('consignee_id', $cargo->consignee_id)
        ->where('customer_id', $cargo->customer_id)
        ->get();

    if ($cargos->isEmpty()) {
        return back()->with('error', 'Nav atrasta neviena krava šim pārim (no cargos found).');
    }

    $shipper   = $cargos->first()->shipper;
    $consignee = $cargos->first()->consignee;
    $customer  = $cargos->first()->customer;

    // 🧾 Единый номер (совпадает с CMR и ORDER)
    $invoiceNr = $this->getOrCreateOrderNumber($trip, $cargos);

    // 📆 Даты
    $invoiceDate = now();
    $paymentTerms = $cargos->firstWhere('payment_terms', '!=', null)?->payment_terms ?? null;
    $dueDate = $paymentTerms ? Carbon::parse($paymentTerms) : $invoiceDate->copy()->addDays(7);

    // 💶 Итоги
    $totals = \App\Helpers\CalculateTax::forCargos($cargos);
    $subtotal = $totals['subtotal'];
    $vat = $totals['vat'];
    $total = $totals['total'];
    $sumInWords = $this->numberToWordsLv($total);

    // 💰 Плательщик
    $payerType = $cargo->payer_type_id;
    $payerLabel = config("payers.$payerType.label") ?? 'Unknown';
    switch ($payerType) {
        case 1: $payer = $cargo->shipper; break;
        case 2: $payer = $cargo->consignee; break;
        case 3: $payer = $cargo->customer; break;
        default: $payer = null; break;
    }

    // 🧾 ISO-коды стран загрузки и разгрузки (берём из первого груза пары)
    $firstCargo = $cargos->first();
    $loadingCountryIso   = getCountryIsoById($firstCargo->loading_country_id);
    $unloadingCountryIso = getCountryIsoById($firstCargo->unloading_country_id);

    // 🧾 Формируем массив для шаблона
    $data = [
        'invoice_nr'   => $invoiceNr,
        'order_nr'   => $invoiceNr,
        'invoice_date' => $invoiceDate->format('d.m.Y'),
        'due_date'     => $dueDate->format('d.m.Y'),

        'expeditor' => [
            'name'    => $trip->expeditor_name ?? '—',
            'reg_nr'  => $trip->expeditor_reg_nr ?? '—',
            'address' => $trip->expeditor_address ?? '—',
            'city'    => $trip->expeditor_city ?? '—',
            'country' => $trip->expeditor_country ?? '—',
            'phone'   => $trip->expeditor_phone ?? '',
            'email'   => $trip->expeditor_email ?? '',
        ],

        'payer' => [
            'label'   => $payerLabel,
            'name'    => $payer?->company_name ?? '—',
            'reg_nr'  => $payer?->reg_nr ?? '—',
            'address' => $payer?->jur_address ?? $payer?->fiz_address ?? '—',
            'city'    => getCityById((int)($payer?->jur_city_id ?? $payer?->fiz_city_id)),
            'country' => getCountryById((int)($payer?->jur_country_id ?? $payer?->fiz_country_id)),
        ],

        'shipper'   => $shipper,
        'consignee' => $consignee,
        'customer'  => $customer,

        'cargos'    => $cargos,
        'sum_in_words' => $sumInWords,
        'subtotal'  => $subtotal,
        'vat'       => $vat,
        'total'     => $total,
        'trip'      => $trip,

        // ✳️ Новые поля для шаблона
        'loading_country_iso'   => $loadingCountryIso,
        'unloading_country_iso' => $unloadingCountryIso,
    ];

    // 🗂️ Папка и имя файла
    $dir = "invoices/trip_{$trip->id}";
    $fileName = "invoice_{$cargo->shipper_id}_{$cargo->consignee_id}.pdf";
    Storage::disk('public')->makeDirectory($dir);

    // 🧾 PDF
    $pdf = Pdf::loadView('pdf.invoice-template', $data)
        ->setPaper('A4')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

    Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

    foreach ($cargos as $c) {
        $c->update([
            'inv_nr'         => $invoiceNr,
            'inv_file'       => "{$dir}/{$fileName}",
            'inv_created_at' => now(),
        ]);
    }

    \Log::info('✅ Invoice PDF generated successfully', [
        'trip' => $trip->id,
        'path' => "{$dir}/{$fileName}",
    ]);

    return asset("storage/{$dir}/{$fileName}");
}



}
