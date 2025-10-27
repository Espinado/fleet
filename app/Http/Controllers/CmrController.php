<?php

namespace App\Http\Controllers;

use App\Models\TripCargo;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CmrController extends Controller
{
    public function generateAndSave(TripCargo $cargo)
    {
        $trip = $cargo->trip;

        // 🚫 Если CMR уже существует, просто открываем PDF
        if ($cargo->cmr_file && Storage::disk('public')->exists(str_replace('storage/', '', $cargo->cmr_file))) {
            return redirect(asset($cargo->cmr_file));
        }

        // === Подготовка данных ===
        $data = [
            // === 1. Отправитель ===
            'sender' => [
                'name' => optional($cargo->shipper)->company_name
                    ?? optional($trip)->expeditor_name
                    ?? '—',
                'reg_nr' => optional($cargo->shipper)->reg_nr,
                'address' => optional($cargo->shipper)->fiz_address
                    ?? optional($cargo->shipper)->jur_address,
                'city' => optional($cargo->shipper)->fiz_city
                    ?? optional($cargo->shipper)->jur_city,
                'country' => optional($cargo->shipper)->fiz_country
                    ?? optional($cargo->shipper)->jur_country,
                'email' => optional($cargo->shipper)->email,
                'phone' => optional($cargo->shipper)->phone,
            ],

            // === 2. Получатель ===
            'receiver' => [
                'name' => optional($cargo->consignee)->company_name ?? '—',
                'reg_nr' => optional($cargo->consignee)->reg_nr,
                'address' => optional($cargo->consignee)->fiz_address
                    ?? optional($cargo->consignee)->jur_address,
                'city' => optional($cargo->consignee)->fiz_city
                    ?? optional($cargo->consignee)->jur_city,
                'country' => optional($cargo->consignee)->fiz_country
                    ?? optional($cargo->consignee)->jur_country,
                'email' => optional($cargo->consignee)->email,
                'phone' => optional($cargo->consignee)->phone,
            ],

            // === 3. Перевозчик / Экспедитор ===
            'carrier' => [
                'name' => $trip->expeditor_name ?? '—',
                'reg_nr' => $trip->expeditor_reg_nr ?? '—',
                'address' => $trip->expeditor_address ?? '—',
                'city' => $trip->expeditor_city ?? '—',
                'country' => $trip->expeditor_country ?? '—',
                'email' => $trip->expeditor_email ?? '—',
                'phone' => $trip->expeditor_phone ?? '—',
            ],

            // === 4. Места ===
            'loading_place' => trim(
                collect([
                    getCityById((int)$cargo->loading_city_id, (int)$cargo->loading_country_id),
                    getCountryById((int)$cargo->loading_country_id),
                    $cargo->loading_address,
                ])->filter()->implode(', ')
            ),

            'unloading_place' => trim(
                collect([
                    getCityById((int)$cargo->unloading_city_id, (int)$cargo->unloading_country_id),
                    getCountryById((int)$cargo->unloading_country_id),
                    $cargo->unloading_address,
                ])->filter()->implode(', ')
            ),

            // === 5. Таблица груза ===
            'items' => [[
                'marks'  => $cargo->cargo_marks ?? '',
                'qty'    => $cargo->cargo_packages ?? '',
                'pack'   => '',
                'desc'   => $cargo->cargo_description ?? '',
                'stat'   => '',
                'gross'  => $cargo->cargo_weight ?? '',
                'volume' => $cargo->cargo_volume ?? '',
            ]],
        ];

        // === Генерация PDF ===
        $pdf = Pdf::loadView('pdf.cmr-template', $data)
            ->setPaper('A4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        // === Сохранение ===
       $tripId = $trip?->id ?? 0;
$dir = "cmr/trip_{$tripId}";
Storage::disk('public')->makeDirectory($dir, 0775, true);

$fileName = 'cmr_' . $cargo->id . '_' . Str::slug($cargo->consignee?->company_name ?? 'cargo') . '.pdf';
$fullPath = Storage::disk('public')->path("{$dir}/{$fileName}");
$pdf->save($fullPath);

$cargo->update([
    'cmr_file' => "storage/{$dir}/{$fileName}",
    'cmr_created_at' => now(),
]);

return response()->json([
    'success' => true,
    'file' => asset("storage/{$dir}/{$fileName}")
]);
    }
}
