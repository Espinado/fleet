<?php

namespace App\Http\Controllers;

use App\Models\TripCargo;
use App\Models\TripStep;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class CmrController extends Controller
{
    public function generateAndSave(TripCargo $cargo): string
    {
        $cargo->loadMissing([
            'trip.driver',
            'trip.truck',
            'trip.trailer',
            'shipper',
            'consignee',
            'items',
            'steps',
        ]);

        $trip = $cargo->trip;

        $cmrNr = trim((string)($cargo->cmr_nr ?? ''));
        if ($cmrNr === '') {
            throw ValidationException::withMessages([
                "cmrNr.{$cargo->id}" => "Укажи CMR номер перед генерацией.",
            ]);
        }

        if ($cargo->items->isEmpty()) {
            throw ValidationException::withMessages([
                "cmrNr.{$cargo->id}" => "Нельзя сгенерировать CMR: нет позиций (items).",
            ]);
        }

        // ✅ items ВАЖНО: шаблон читает $item['...'], поэтому делаем массивы
        $items = $cargo->items->map(function ($item) {
            return [
                'marks'          => '',
                'packages'       => $item->packages !== null ? (int)$item->packages : null,
                'package_type'   => '',

                'description'    => (string)($item->description ?? ''),
                'customs_code'   => (string)($item->customs_code ?? ''),

                'gross_weight'   => $item->gross_weight !== null ? (float)$item->gross_weight : null,
                'net_weight'     => $item->net_weight !== null ? (float)$item->net_weight : null,
                'volume'         => $item->volume !== null ? (float)$item->volume : null,
                'loading_meters' => $item->loading_meters !== null ? (float)$item->loading_meters : null,

                'pallets'        => $item->pallets !== null ? (int)$item->pallets : null,
                'units'          => $item->units !== null ? (int)$item->units : null,
                'tonnes'         => $item->tonnes !== null ? (float)$item->tonnes : null,
            ];
        })->values();

        // ✅ totals
        $totals = [
            'packages' => (int) $items->sum(fn ($i) => (int)($i['packages'] ?? 0)),
            'pallets'  => (int) $items->sum(fn ($i) => (int)($i['pallets'] ?? 0)),
            'units'    => (int) $items->sum(fn ($i) => (int)($i['units'] ?? 0)),
            'volume'   => (float)$items->sum(fn ($i) => (float)($i['volume'] ?? 0)),
            'lm'       => (float)$items->sum(fn ($i) => (float)($i['loading_meters'] ?? 0)),
            'tonnes'   => (float)$items->sum(fn ($i) => (float)($i['tonnes'] ?? 0)),
            'net_kg'   => (float)$items->sum(fn ($i) => (float)($i['net_weight'] ?? 0)),
            'gross_kg' => (float)$items->sum(fn ($i) => (float)($i['gross_weight'] ?? 0)),
        ];

        // ✅ места загрузки/выгрузки из steps
        $loadingPlaces = $cargo->steps
            ->where('type', 'loading')
            ->unique('id')
            ->map(function (TripStep $s) {
                return getCityById($s->city_id, $s->country_id)
                    . ', ' . getCountryById($s->country_id)
                    . ($s->address ? ' — ' . $s->address : '');
            })
            ->values()
            ->toArray();

        $unloadingPlaces = $cargo->steps
            ->where('type', 'unloading')
            ->unique('id')
            ->map(function (TripStep $s) {
                return getCityById($s->city_id, $s->country_id)
                    . ', ' . getCountryById($s->country_id)
                    . ($s->address ? ' — ' . $s->address : '');
            })
            ->values()
            ->toArray();

        // ✅ container/seal отдельными значениями
        $containerNr = !empty($trip->cont_nr) ? $trip->cont_nr : null;
        $sealNr      = !empty($trip->seal_nr) ? $trip->seal_nr : null;

        $data = [
            'cmr_nr' => $cmrNr,

            'sender'   => $cargo->shipper,
            'receiver' => $cargo->consignee,

            'trip' => $trip,

            'loading_places'   => $loadingPlaces,
            'unloading_places' => $unloadingPlaces,

            'supplier_invoice_nr' => $cargo->supplier_invoice_nr,
            'order_nr'            => $cargo->order_nr,
            'inv_nr'              => $cargo->inv_nr,

            'container_nr' => $containerNr,
            'seal_nr'      => $sealNr,

            'items'  => $items,
            'totals' => $totals,

            'date' => now()->format('d.m.Y'),
        ];

        $dir = "cmr/trip_{$trip->id}";
        $fileName = "cmr_cargo_{$cargo->id}.pdf";

        Storage::disk('public')->makeDirectory($dir);

        $pdf = Pdf::loadView('pdf.cmr-template', $data)
            ->setPaper('A4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

        $cargo->update([
            'cmr_file'       => "{$dir}/{$fileName}",
            'cmr_created_at' => now(),
            'cmr_nr'         => $cmrNr,
        ]);

        Log::info('✅ CMR PDF generated successfully (single cargo)', [
            'trip'  => $trip->id,
            'cargo' => $cargo->id,
            'path'  => "{$dir}/{$fileName}",
        ]);

        return asset("storage/{$dir}/{$fileName}");
    }

    /**
     * Алиас на случай если где-то уже дергают generateInvoice()
     */
    public function generateInvoice(TripCargo $cargo): string
    {
        return $this->generateInvoiceAndSave($cargo);
    }

    /**
     * ✅ Новый метод под роут: /invoice/{cargo}/generate
     */
 public function generateInvoiceAndSave(TripCargo $cargo): string
{
    $cargo->loadMissing([
        'trip.truck',
        'trip.trailer',
        'customer',
        'shipper',
        'consignee',
        'steps',
    ]);

    $trip = $cargo->trip;

    // ✅ INV номер обязателен (пока вручную задаёте)
    $invoiceNr = trim((string)($cargo->inv_nr ?? ''));
    if ($invoiceNr === '') {
        throw ValidationException::withMessages([
            "invNr.{$cargo->id}" => "Укажи INV номер перед генерацией инвойса.",
        ]);
    }

    // ✅ PAYER строго по payer_type_id (config/payers.php)
    $payerTypeId = (int)($cargo->payer_type_id ?? 0);
    if (!$payerTypeId) {
        throw ValidationException::withMessages([
            "payerType.{$cargo->id}" => "Укажи плательщика (payer_type_id) перед генерацией инвойса.",
        ]);
    }

    $payerClient = match ($payerTypeId) {
        1 => $cargo->shipper,
        2 => $cargo->consignee,
        3 => $cargo->customer,
        default => null,
    };

    if (!$payerClient) {
        $label = config("payers.{$payerTypeId}.label", 'Unknown');
        throw ValidationException::withMessages([
            "payerType.{$cargo->id}" => "Плательщик '{$label}' не задан: заполни нужного клиента (shipper/consignee/customer) в cargo.",
        ]);
    }

    // ✅ базовые проверки сумм (чтобы не генерить пустые счета)
    $subtotal = (float)($cargo->price ?? 0);
    if ($subtotal <= 0) {
        throw ValidationException::withMessages([
            "price.{$cargo->id}" => "Укажи стоимость (price) перед генерацией инвойса.",
        ]);
    }

    // ✅ налог: используем snapshot из cargo, иначе считаем
    $taxPercent = $cargo->tax_percent !== null ? (float)$cargo->tax_percent : null;

    if ($cargo->total_tax_amount !== null && (float)$cargo->total_tax_amount > 0) {
        $vat = (float)$cargo->total_tax_amount;
    } elseif ($taxPercent !== null) {
        $vat = round($subtotal * ($taxPercent / 100), 2);
    } else {
        $vat = 0.00;
    }

    if ($cargo->price_with_tax !== null && (float)$cargo->price_with_tax > 0) {
        $total = (float)$cargo->price_with_tax;
    } else {
        $total = round($subtotal + $vat, 2);
    }

    $currency = (string)($cargo->currency ?: 'EUR');

    // ✅ expeditor snapshot из trip (FIX bank field)
    $expeditor = [
        'name'      => (string)($trip->expeditor_name ?? ''),
        'reg_nr'    => (string)($trip->expeditor_reg_nr ?? ''),
        'address'   => (string)($trip->expeditor_address ?? ''),
        'city'      => (string)($trip->expeditor_city ?? ''),
        'country'   => (string)($trip->expeditor_country ?? ''),
        'email'     => (string)($trip->expeditor_email ?? ''),
        'phone'     => (string)($trip->expeditor_phone ?? ''),
        'bank_name' => (string)($trip->expeditor_bank ?? ''),
        'bic'       => (string)($trip->expeditor_bic ?? ''),
        'iban'      => (string)($trip->expeditor_iban ?? ''),
    ];

    // ✅ payer snapshot
    $countryId = (int)($payerClient->jur_country_id ?? $payerClient->fiz_country_id ?? 0);
    $cityId    = (int)($payerClient->jur_city_id ?? $payerClient->fiz_city_id ?? 0);

    $payer = [
        'label'   => (string)config("payers.{$payerTypeId}.label", 'Klients'),
        'name'    => (string)($payerClient->company_name ?? ''),
        'reg_nr'  => (string)($payerClient->reg_nr ?? ''),
        'address' => (string)($payerClient->jur_address ?? $payerClient->fiz_address ?? ''),
        'city'    => ($cityId && $countryId) ? (string)getCityById($cityId, $countryId) : '',
        'country' => $countryId ? (string)getCountryById($countryId) : '',
    ];

    // ✅ даты загрузки/выгрузки (минимально)
    $firstLoading = $cargo->steps
        ->where('type', 'loading')
        ->sortBy(fn ($s) => $s->date?->timestamp ?? PHP_INT_MAX)
        ->first();

    $lastUnloading = $cargo->steps
        ->where('type', 'unloading')
        ->sortByDesc(fn ($s) => $s->date?->timestamp ?? 0)
        ->first();

    $firstLoadingDate  = $firstLoading?->date;
    $lastUnloadingDate = $lastUnloading?->date;

    // ✅ пока ISO не используем
    $loading_country_iso   = '';
    $unloading_country_iso = '';

    $sumInWords = $this->moneyToWordsLv((float)$total);

    $issuedAt = now();

    $data = [
        'invoice_date' => $issuedAt->format('d.m.Y'),
        'invoice_nr'   => $invoiceNr,
        'currency'     => $currency,

        'expeditor' => $expeditor,
        'payer'     => $payer,

        'supplier_invoice_nr' => (string)($cargo->supplier_invoice_nr ?? ''),
        'order_nr'            => (string)($cargo->order_nr ?? ''),

        'trip'   => $trip,
        'cargos' => collect([$cargo]),

        'first_loading_date'  => $firstLoadingDate,
        'last_unloading_date' => $lastUnloadingDate,

        'loading_country_iso'   => $loading_country_iso,
        'unloading_country_iso' => $unloading_country_iso,

        'subtotal'     => $subtotal,
        'vat'          => $vat,
        'total'        => $total,
        'sum_in_words' => $sumInWords,
    ];

    $dir = "invoice/trip_{$trip->id}";
    $fileName = "invoice_cargo_{$cargo->id}.pdf";

    Storage::disk('public')->makeDirectory($dir);

    $pdf = Pdf::loadView('pdf.invoice-template', $data)
        ->setPaper('A4')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'DejaVu Sans',
        ]);

    Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

    // ✅ 1) кеш в trip_cargos (как раньше)
    $cargo->update([
        'inv_file'       => "{$dir}/{$fileName}",
        'inv_created_at' => $issuedAt,
        'inv_nr'         => $invoiceNr,
    ]);

    // ✅ 2) ИСТИНА: запись в invoices (если модель уже создана)
    // ВАЖНО: добавь "use App\Models\Invoice;" наверху файла
    \App\Models\Invoice::updateOrCreate(
        ['trip_cargo_id' => $cargo->id],
        [
            'trip_id'         => $trip->id,
            'invoice_no'      => $invoiceNr,
            'issued_at'       => $issuedAt,
            'due_date'        => $cargo->payment_terms,

            'payer_type_id'   => $payerTypeId,
            'payer_client_id' => $payerClient->id ?? null,

            'currency'        => $currency,
            'subtotal'        => $subtotal,
            'tax_percent'     => $cargo->tax_percent,
            'tax_total'       => $vat,
            'total'           => $total,

            'pdf_file'        => "{$dir}/{$fileName}",
        ]
    );

    Log::info('✅ INVOICE PDF generated successfully (single cargo)', [
        'trip'            => $trip->id,
        'cargo'           => $cargo->id,
        'path'            => "{$dir}/{$fileName}",
        'invoice_no'      => $invoiceNr,
        'currency'        => $currency,
        'subtotal'        => $subtotal,
        'vat'             => $vat,
        'total'           => $total,
        'payer_type_id'   => $payerTypeId,
        'payer_label'     => config("payers.{$payerTypeId}.label"),
        'payer_client_id' => $payerClient->id ?? null,
    ]);

    return asset("storage/{$dir}/{$fileName}");
}
    private function moneyToWordsLv(float $amount): string
{
    $amount = round($amount, 2);

    $euros = (int) floor($amount);
    $cents = (int) round(($amount - $euros) * 100);

    $fmt = new \NumberFormatter('lv_LV', \NumberFormatter::SPELLOUT);

    // NumberFormatter возвращает слова в нижнем регистре — это ок для инвойса
    $eurosWords = $fmt->format($euros);

    // EUR/CENT можно оставить как "EUR" / "centi" (в Латвии обычно норм)
    return trim($eurosWords) . " euro " . str_pad((string)$cents, 2, '0', STR_PAD_LEFT) . " centi";
}
}
