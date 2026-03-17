<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Truck;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExpiringDocumentsService
{
    public static function list(int $daysAhead = 30): Collection
    {
        $today = Carbon::today();
        $deadline = $today->copy()->addDays($daysAhead);
        $list = collect();

        $truckFields = [
            'inspection_expired' => 'expiring_docs.inspection',
            'insurance_expired' => 'expiring_docs.insurance',
            'license_expired' => 'expiring_docs.license',
            'tech_passport_expired' => 'expiring_docs.tech_passport',
        ];

        $trucks = Truck::query()->get();

        foreach ($trucks as $truck) {
            $name = trim(($truck->brand ?? '') . ' ' . ($truck->model ?? '') . ' ' . ($truck->plate ?? ''));
            foreach ($truckFields as $field => $labelKey) {
                $date = $truck->{$field} ?? null;
                if ($date && Carbon::parse($date)->lte($deadline)) {
                    $list->push((object) [
                        'doc_type' => $field,
                        'doc_label_key' => $labelKey,
                        'entity_type' => 'truck',
                        'entity_id' => $truck->id,
                        'entity_name' => $name,
                        'expires_at' => Carbon::parse($date),
                        'is_overdue' => Carbon::parse($date)->isPast(),
                    ]);
                }
            }
        }

        $trailerFields = [
            'inspection_expired' => 'expiring_docs.inspection',
            'insurance_expired' => 'expiring_docs.insurance',
            'tech_passport_expired' => 'expiring_docs.tech_passport',
            'tir_expired' => 'expiring_docs.tir',
        ];

        $trailers = Trailer::query()->get();

        foreach ($trailers as $trailer) {
            $name = trim(($trailer->brand ?? '') . ' ' . ($trailer->plate ?? ''));
            foreach ($trailerFields as $field => $labelKey) {
                $date = $trailer->{$field} ?? null;
                if ($date && Carbon::parse($date)->lte($deadline)) {
                    $list->push((object) [
                        'doc_type' => $field,
                        'doc_label_key' => $labelKey,
                        'entity_type' => 'trailer',
                        'entity_id' => $trailer->id,
                        'entity_name' => $name,
                        'expires_at' => Carbon::parse($date),
                        'is_overdue' => Carbon::parse($date)->isPast(),
                    ]);
                }
            }
        }

        $driverFields = [
            'license_end' => 'expiring_docs.license',
            'code95_end' => 'expiring_docs.code95',
            'permit_expired' => 'expiring_docs.permit',
            'medical_expired' => 'expiring_docs.medical',
            'declaration_expired' => 'expiring_docs.declaration',
            'medical_exam_expired' => 'expiring_docs.medical_exam',
        ];

        $drivers = Driver::query()->get();

        foreach ($drivers as $driver) {
            $name = $driver->full_name ?? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));
            foreach ($driverFields as $field => $labelKey) {
                $date = $driver->{$field} ?? null;
                if ($date && Carbon::parse($date)->lte($deadline)) {
                    $list->push((object) [
                        'doc_type' => $field,
                        'doc_label_key' => $labelKey,
                        'entity_type' => 'driver',
                        'entity_id' => $driver->id,
                        'entity_name' => $name,
                        'expires_at' => Carbon::parse($date),
                        'is_overdue' => Carbon::parse($date)->isPast(),
                    ]);
                }
            }
        }

        return $list->sortBy('expires_at')->values();
    }
}
