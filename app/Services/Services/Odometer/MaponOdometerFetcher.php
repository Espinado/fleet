<?php

namespace App\Services\Services\Odometer;

use App\Services\Services\MaponService;
use Carbon\Carbon;

class MaponOdometerFetcher
{
    public function __construct(
        protected MaponService $mapon
    ) {}

    /**
     * Универсальный одометр:
     * 1) CAN odom (если есть)
     * 2) fallback: mileage (обычно в метрах -> км)
     */
    public function fetchOdometer(int $unitId, int $companyId): ?array
    {
         $unit = $this->mapon->getUnitData(
        (int) $unitId,
        (int) $companyId,
        'can'
    );

    if (!is_array($unit)) {
        return null;
    }

    $lastUpdate = (string) (data_get($unit, 'last_update') ?? '');

        // 1) CAN
        $canKm = data_get($unit, 'can.odom.value');
        if ($canKm !== null && $canKm !== '') {
            $canAt = (string) (data_get($unit, 'can.odom.gmt') ?? '');
            return $this->decorate([
                'km'       => (float) $canKm,
                'source'   => 'can',
                'mapon_at' => $canAt !== '' ? $canAt : ($lastUpdate !== '' ? $lastUpdate : null),
                'raw'      => $unit,
            ]);
        }

        // 2) mileage fallback
        $mileageRaw = data_get($unit, 'mileage');
        if ($mileageRaw !== null && $mileageRaw !== '') {
            return $this->decorate([
                'km'       => ((float) $mileageRaw) / 1000, // метры -> км (по твоим данным)
                'source'   => 'mileage',
                'mapon_at' => $lastUpdate !== '' ? $lastUpdate : null,
                'raw'      => $unit,
            ]);
        }

        // ничего нет
        return $this->decorate([
            'km'       => null,
            'source'   => null,
            'mapon_at' => $lastUpdate !== '' ? $lastUpdate : null,
            'raw'      => $unit,
        ]);
    }

    protected function decorate(array $data): array
    {
        $data['is_stale'] = false;
        $data['stale_minutes'] = null;

        $maponAt = $data['mapon_at'] ?? null;
        if (!$maponAt) {
            return $data;
        }

        try {
            $at = Carbon::parse((string) $maponAt);
            $minutes = $at->diffInMinutes(now());

            $threshold = (int) config('mapon.can_stale_minutes', 30);

            $data['stale_minutes'] = $minutes;
            $data['is_stale'] = $threshold > 0 && $minutes >= $threshold;
        } catch (\Throwable $e) {
            // ignore parse errors
        }

        return $data;
    }
}
