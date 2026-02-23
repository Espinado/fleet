<?php

namespace App\Http\Controllers;

use App\Models\TripCargo;
use App\Models\TripStep;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CmrController extends Controller
{
    /**
     * Единый номер для пары (shipper->consignee) в рамках trip.
     * Если уже есть — используем. Если нет — создаём и сохраняем во все грузы пары.
     *
     * Формат (как у тебя было): [PLATE_NO_SPACES]/[TRIP_ID]
     */
    private function getOrCreateOrderNumber($trip, $cargos): string
    {
        // Важно: firstWhere умеет только "ключ=значение", поэтому ищем вручную
        $existingOrderNr = $cargos->first(fn ($c) => !empty($c->order_nr))?->order_nr;

        if (!empty($existingOrderNr)) {
            return $existingOrderNr;
        }

        $plate = str_replace(' ', '', $trip->truck->plate ?? 'NO_PLATE');
        $orderNr = $plate . '/' . $trip->id;

        foreach ($cargos as $c) {
            $c->update(['order_nr' => $orderNr]);
        }

        return $orderNr;
    }

    /**
     * (упрощённая) сумма прописью на латышском — как было у тебя.
     * Если нужно доработать (миллионы, центы, склонения) — сделаем отдельно.
     */
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

        $n = (int) floor((float) $amount);
        if ($n === 0) return 'Nulle EUR, 00 centi';

        $words = [];

        if ($n >= 1000) {
            $words[] = $units[(int)($n / 1000)] . ' tūkstoši';
            $n %= 1000;
        }

        if ($n >= 100) {
            $words[] = $units[(int)($n / 100)] . ' simti';
            $n %= 100;
        }

        if ($n >= 20) {
            $words[] = $tens[(int)(floor($n / 10) * 10)];
            $n %= 10;
        }

        if ($n > 0) {
            $words[] = $units[$n] ?? (string)$n;
        }

        return ucfirst(trim(implode(' ', $words))) . ' EUR, 00 centi';
    }

    /**
     * CMR для пары shipper -> consignee (в рамках trip).
     * Возвращает URL на файл в storage/public.
     */
    public function generateAndSave(TripCargo $cargo)
    {
        $trip = $cargo->trip;

        // 1) Грузы для пары shipper -> consignee
        $cargos = $trip->cargos()
            ->where('shipper_id', $cargo->shipper_id)
            ->where('consignee_id', $cargo->consignee_id)
            ->with(['items', 'steps', 'shipper', 'consignee', 'trip.driver', 'trip.truck', 'trip.trailer'])
            ->get();

        if ($cargos->isEmpty()) {
            return back()->with('error', 'No cargos found for this pair.');
        }

        $totalPriceWithTax = (float) $cargos->sum('price_with_tax');

        // 2) Order / CMR number (единый)
        $cmrNr = $this->getOrCreateOrderNumber($trip, $cargos);

        // 3) Клиенты
        $shipper   = $cargos->first()->shipper;
        $consignee = $cargos->first()->consignee;

        // 4) Собираем items (только непустые поля)
        $items = [];
        foreach ($cargos as $c) {
            foreach ($c->items as $item) {
                $fields = [
                    'description'     => $item->description,
                    'packages'        => $item->packages,
                    'pallets'         => $item->pallets,
                    'units'           => $item->units,
                    'net_weight'      => $item->net_weight,
                    'gross_weight'    => $item->gross_weight,
                    'tonnes'          => $item->tonnes,
                    'volume'          => $item->volume,
                    'loading_meters'  => $item->loading_meters,
                    'hazmat'          => $item->hazmat,
                    'temperature'     => $item->temperature,
                    'stackable'       => $item->stackable,
                    'instructions'    => $item->instructions,
                    'remarks'         => $item->remarks,
                    'price'           => $item->price,
                    'tax_percent'     => $item->tax_percent,
                    'tax_amount'      => $item->tax_amount,
                    'price_with_tax'  => $item->price_with_tax,
                ];

                $filtered = [];
                foreach ($fields as $key => $value) {
                    // оставляем true/false, но убираем null/''/0
                    if ($value === null) continue;
                    if ($value === '') continue;
                    if ($value === 0 || $value === 0.0) continue;

                    $filtered[$key] = $value;
                }

                if (!empty($filtered)) {
                    $items[] = $filtered;
                }
            }
        }

        if (empty($items)) {
            return back()->with('error', 'No cargo items found for this client pair.');
        }

        // 5) Места загрузки / выгрузки через steps
        $loadingSteps = collect();
        $unloadingSteps = collect();

        foreach ($cargos as $c) {
            foreach ($c->steps as $step) {
                if ($step->type === 'loading') {
                    $loadingSteps->push($step);
                } elseif ($step->type === 'unloading') {
                    $unloadingSteps->push($step);
                }
            }
        }

        $loadingPlaces = $loadingSteps
            ->unique('id')
            ->map(function (TripStep $s) {
                return getCityById($s->city_id, $s->country_id)
                    . ', ' . getCountryById($s->country_id)
                    . ($s->address ? ' — ' . $s->address : '');
            })
            ->values()
            ->toArray();

        $unloadingPlaces = $unloadingSteps
            ->unique('id')
            ->map(function (TripStep $s) {
                return getCityById($s->city_id, $s->country_id)
                    . ', ' . getCountryById($s->country_id)
                    . ($s->address ? ' — ' . $s->address : '');
            })
            ->values()
            ->toArray();

        // 6) Данные для PDF
        $data = [
            'sender' => [
                'name'    => $shipper->company_name ?? '—',
                'address' => $shipper->fiz_address ?? $shipper->jur_address ?? '—',
                'city'    => getCityById(
                    (int)($shipper->fiz_city_id ?? $shipper->jur_city_id),
                    (int)($shipper->fiz_country_id ?? $shipper->jur_country_id)
                ),
                'country' => getCountryById(
                    (int)($shipper->fiz_country_id ?? $shipper->jur_country_id)
                ),
                'reg_nr'  => $shipper->reg_nr ?? '—',
            ],

            'receiver' => [
                'name'    => $consignee->company_name ?? '—',
                'address' => $consignee->fiz_address ?? $consignee->jur_address ?? '—',
                'city'    => getCityById(
                    (int)($consignee->fiz_city_id ?? $consignee->jur_city_id),
                    (int)($consignee->fiz_country_id ?? $consignee->jur_country_id)
                ),
                'country' => getCountryById(
                    (int)($consignee->fiz_country_id ?? $consignee->jur_country_id)
                ),
                'reg_nr'  => $consignee->reg_nr ?? '—',
            ],

            'carrier' => [
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

            'loading_places'         => $loadingPlaces,
            'unloading_places'       => $unloadingPlaces,
            'items'                  => $items,
            'date'                   => now()->format('d.m.Y'),
            'trip_id'                => $trip->id,
            'cmr_nr'                 => $cmrNr,
            'total_price_with_tax'   => $totalPriceWithTax,
        ];

        // 7) Сохранение PDF
        $dir = "cmr/trip_{$trip->id}";
        $fileName = "cmr_{$cargo->shipper_id}_{$cargo->consignee_id}.pdf";

        Storage::disk('public')->makeDirectory($dir);

        $pdf = Pdf::loadView('pdf.cmr-template', $data)
            ->setPaper('A4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

        // обновляем все грузы пары
        foreach ($cargos as $c) {
            $c->update([
                'cmr_file'       => "cmr/trip_{$trip->id}/{$fileName}",
                'cmr_created_at' => now(),
                'cmr_nr'         => $cmrNr,
            ]);
        }

        return asset("storage/{$dir}/{$fileName}");
    }

    /**
     * Transport Order для тройки shipper->consignee->customer (в рамках trip).
     */
    public function generateTransportOrder(TripCargo $cargo)
    {
        $trip = $cargo->trip;

        $cargos = $trip->cargos()
            ->where('shipper_id', $cargo->shipper_id)
            ->where('consignee_id', $cargo->consignee_id)
            ->where('customer_id', $cargo->customer_id)
            ->with(['shipper', 'consignee', 'customer', 'trip.driver', 'trip.truck', 'trip.trailer'])
            ->get();

        if ($cargos->isEmpty()) {
            return back()->with('error', 'Nav atrasta neviena krava šim pārim.');
        }

        $shipper   = $cargos->first()->shipper;
        $consignee = $cargos->first()->consignee;
        $customer  = $cargos->first()->customer;

        $paymentTerms = $cargos->first(fn ($c) => !empty($c->payment_terms))?->payment_terms;

        $orderNr = $this->getOrCreateOrderNumber($trip, $cargos);
        $totalPriceWithTax = (float) $cargos->sum('price_with_tax');

        // ВАЖНО: в order у тебя ещё используются старые поля loading_city_id и т.п.
        // Если ты уже перешёл на steps — мы перепишем это на steps отдельно.
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

            'date'                => now()->format('d.m.Y'),
            'trip'                => $trip,
            'order_nr'            => $orderNr,
            'payment_terms'       => $paymentTerms,
            'total_price_with_tax'=> $totalPriceWithTax,
        ];

        $dir = "orders/order_{$trip->id}";
        $fileName = "transport_order.pdf";

        Storage::disk('public')->makeDirectory($dir);

        $pdf = Pdf::loadView('pdf.transport-order', $data)
            ->setPaper('A4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

        foreach ($cargos as $c) {
            $c->update([
                'order_file'       => "{$dir}/{$fileName}",
                'order_created_at' => now(),
                'order_nr'         => $orderNr,
            ]);
        }

        return asset("storage/{$dir}/{$fileName}");
    }

    /**
     * Invoice для тройки shipper->consignee->customer (в рамках trip).
     * ВАЖНО: плательщик всегда customer_id (как ты сказал).
     */
    public function generateInvoice(TripCargo $cargo)
    {
        $trip = $cargo->trip;

        $cargos = $trip->cargos()
            ->where('shipper_id', $cargo->shipper_id)
            ->where('consignee_id', $cargo->consignee_id)
            ->where('customer_id', $cargo->customer_id)
            ->with(['shipper', 'consignee', 'customer', 'steps', 'trip.driver', 'trip.truck', 'trip.trailer'])
            ->get();

        if ($cargos->isEmpty()) {
            return back()->with('error', 'Nav atrasta neviena krava šim pārim (no cargos found).');
        }

        $shipper   = $cargos->first()->shipper;
        $consignee = $cargos->first()->consignee;
        $customer  = $cargos->first()->customer;

        if (!$customer) {
            return back()->with('error', 'Customer not found for invoice payer.');
        }

        // Единый номер (совпадает с ORDER и CMR)
        $invoiceNr = $this->getOrCreateOrderNumber($trip, $cargos);

        // Даты
        $invoiceDate  = now();
        $paymentTerms = $cargos->first(fn ($c) => !empty($c->payment_terms))?->payment_terms ?? null;
        $dueDate      = $paymentTerms ? Carbon::parse($paymentTerms) : $invoiceDate->copy()->addDays(7);

        // Даты рейса (через steps): первая загрузка и последняя выгрузка
        $allSteps = $cargos->flatMap(fn ($c) => $c->steps);

        $firstLoading = $allSteps
            ->where('type', 'loading')
            ->sortBy(fn ($s) => ($s->date ?? '9999-12-31') . ' ' . ($s->time ?? '00:00'))
            ->first();

        $lastUnloading = $allSteps
            ->where('type', 'unloading')
            ->sortByDesc(fn ($s) => ($s->date ?? '0000-01-01') . ' ' . ($s->time ?? '00:00'))
            ->first();

        $firstLoadingDate  = $firstLoading?->date;
        $lastUnloadingDate = $lastUnloading?->date;

        // Суммы (оставляю твою helper-логику)
        $totals = \App\Helpers\CalculateTax::forCargos($cargos);
        $subtotal = (float)($totals['subtotal'] ?? 0);
        $vat      = (float)($totals['vat'] ?? 0);
        $total    = (float)($totals['total'] ?? 0);

        $sumInWords = $this->numberToWordsLv($total);

        // Плательщик всегда customer
        $payer = $customer;
        $payerLabel = 'Customer (Pasūtītājs)';

        // ISO-коды стран (по первому cargo — как у тебя было)
        $firstCargo = $cargos->first();
        $loadingCountryIso   = getCountryIsoById($firstCargo->loading_country_id);
        $unloadingCountryIso = getCountryIsoById($firstCargo->unloading_country_id);

        $data = [
            'invoice_nr'   => $invoiceNr,
            'order_nr'     => $invoiceNr,
            'invoice_date' => $invoiceDate->format('d.m.Y'),
            'due_date'     => $dueDate->format('d.m.Y'),

            'first_loading_date'  => $firstLoadingDate,
            'last_unloading_date' => $lastUnloadingDate,

            'expeditor' => [
                'name'      => $trip->expeditor_name ?? '—',
                'reg_nr'    => $trip->expeditor_reg_nr ?? '—',
                'address'   => $trip->expeditor_address ?? '—',
                'city'      => $trip->expeditor_city ?? '—',
                'country'   => $trip->expeditor_country ?? '—',
                'phone'     => $trip->expeditor_phone ?? '',
                'email'     => $trip->expeditor_email ?? '',
                'bank_name' => $trip->expeditor_bank ?? '—',
                'iban'      => $trip->expeditor_iban ?? '—',
                'bic'       => $trip->expeditor_bic ?? '—',
            ],

            // ✅ payer = customer_id
            'payer' => [
                'label'   => $payerLabel,
                'name'    => $payer->company_name ?? '—',
                'reg_nr'  => $payer->reg_nr ?? '—',
                'address' => $payer->jur_address ?? $payer->fiz_address ?? '—',
                'city'    => getCityById(
                    (int)($payer->jur_city_id ?? $payer->fiz_city_id),
                    (int)($payer->jur_country_id ?? $payer->fiz_country_id)
                ),
                'country' => getCountryById(
                    (int)($payer->jur_country_id ?? $payer->fiz_country_id)
                ),
            ],

            'shipper'   => $shipper,
            'consignee' => $consignee,
            'customer'  => $customer,

            'cargos'       => $cargos,
            'sum_in_words' => $sumInWords,
            'subtotal'     => $subtotal,
            'vat'          => $vat,
            'total'        => $total,
            'trip'         => $trip,

            'loading_country_iso'   => $loadingCountryIso,
            'unloading_country_iso' => $unloadingCountryIso,
        ];

        $dir = "invoices/trip_{$trip->id}";
        $fileName = "invoice_{$cargo->shipper_id}_{$cargo->consignee_id}.pdf";

        Storage::disk('public')->makeDirectory($dir);

        $pdf = Pdf::loadView('pdf.invoice-template', $data)
            ->setPaper('A4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

        foreach ($cargos as $c) {
            $c->update([
                'inv_nr'         => $invoiceNr,
                'inv_file'       => "{$dir}/{$fileName}",
                'inv_created_at' => now(),
            ]);
        }

        Log::info('✅ Invoice PDF generated successfully', [
            'trip' => $trip->id,
            'path' => "{$dir}/{$fileName}",
            'payer' => $customer->company_name ?? null,
            'payer_customer_id' => $cargo->customer_id,
        ]);

        return asset("storage/{$dir}/{$fileName}");
    }
}
