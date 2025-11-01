<?php

namespace App\Http\Controllers;

use App\Models\TripCargo;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CmrController extends Controller
{
    public function generateAndSave(TripCargo $cargo)
    {
        $trip = $cargo->trip;

        // 🟢 Находим все грузы для этой пары (shipper → consignee)
        $cargos = $trip->cargos()
            ->where('shipper_id', $cargo->shipper_id)
            ->where('consignee_id', $cargo->consignee_id)
            ->get();

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
        'marks'  => $item->marks ?? '',
        'qty'    => $item->packages ?? '',
        'desc'   => $item->description ?? '',
        'gross'  => $item->weight ?? '',
        'volume' => $item->volume ?? '',
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
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        // 🟢 Сохраняем PDF в storage
        Storage::disk('public')->put("{$dir}/{$fileName}", $pdf->output());

        // 🟢 Обновляем все грузы этой пары
        foreach ($cargos as $c) {
            $c->update([
                'cmr_file'       => "cmr/trip_{$tripId}/{$fileName}",
                'cmr_created_at' => now(),
            ]);
        }

        // 🟢 Возвращаем ссылку для открытия
        return $publicUrl;
    }
}
