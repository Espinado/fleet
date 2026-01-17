<?php

namespace App\Services\Services\Odometer;

use App\Services\Services\MaponService;
use Carbon\Carbon;

class MaponOdometerFetcher
{
    protected MaponService $mapon;

    public function __construct(MaponService $mapon)
    {
        $this->mapon = $mapon;
    }

    /**
     * Получает CAN-одометр из Mapon (can.odom.value)
     *
     * Возвращает:
     * [
     *   'km' => float|null,
     *   'mapon_at' => Carbon|null,
     *   'is_stale' => bool,
     *   'stale_minutes' => int|null,
     *   'raw' => array
     * ]
     */
    public function fetchCanOdometer(int|string $unitId): ?array
{
    $unit = $this->mapon->getUnitData($unitId, 'can');
    if (!is_array($unit)) return null;

    $km  = data_get($unit, 'can.odom.value');
    $gmt = data_get($unit, 'can.odom.gmt');

    $lastUpdate = data_get($unit, 'last_update'); // "2026-01-17T22:46:56Z"
    $lastUpdateAt = !empty($lastUpdate) ? Carbon::parse($lastUpdate) : null;

    $maponAt = !empty($gmt) ? Carbon::parse($gmt) : null;

    $staleMinutes = null;
    $isStale = false;

    if ($maponAt) {
        $staleMinutes = (int) $maponAt->diffInMinutes(now());
        $threshold = (int) config('mapon.can_stale_minutes', 30);
        $isStale = $staleMinutes >= $threshold;
    }

    return [
        'km' => ($km === null || $km === '') ? null : round((float) $km, 1),
        'mapon_at' => $maponAt,
        'last_update_at' => $lastUpdateAt,   // ✅ пригодится в UI
        'is_stale' => $isStale,
        'stale_minutes' => $staleMinutes,
        'raw' => $unit,
    ];
}

}
