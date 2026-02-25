<?php

namespace App\Livewire\Trucks;

use Livewire\Component;
use App\Models\Truck;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\Services\MaponService;
use Carbon\Carbon;

class ShowTruck extends Component
{
    public Truck $truck;

    // Mapon UI fields (CAN only)
    public ?float $maponCanMileageKm = null;     // CAN odometer (km)
    public ?string $maponUnitName = null;
    public ?string $maponError = null;

    // Stale status (configured)
    public bool $maponCanStale = false;
    public ?int $maponCanDaysAgo = null;

    // Meta
    public ?string $maponLastUpdate = null;      // unit.last_update
    public ?string $maponCanAt = null;           // can.odom.gmt

    public function mount(Truck $truck): void
    {
        $this->truck = $truck;
        $this->loadMaponData();
    }

    /**
     * Button handler: clear cache and load fresh data
     */
    public function refreshMaponData(): void
    {
        $unitId = $this->truck->mapon_unit_id ?? null;

        if ($unitId) {
            Cache::forget($this->cacheKey($unitId));
        }

        $this->loadMaponData();
    }

public function loadMaponData(): void
{
    // reset so nothing "sticks"
    $this->maponError = null;
    $this->maponCanMileageKm = null;
    $this->maponUnitName = null;
    $this->maponLastUpdate = null;
    $this->maponCanAt = null;

    $this->maponCanStale = false;
    $this->maponCanDaysAgo = null;

    $unitId = $this->truck->mapon_unit_id ?? null;

    if (!$unitId) {
        $this->maponError = 'mapon_unit_id не задан для данного трака.';
        return;
    }

    // cache учитывает компанию (ключ Mapon разный)
    $companyId = (int) ($this->truck->company ?? 0);
    $cacheKey = $this->cacheKey($unitId) . ':company:' . $companyId;

    $result = Cache::remember($cacheKey, now()->addMinutes(5), function () {
        try {
            /** @var MaponService $svc */
            $svc = app(MaponService::class);

            return $svc->getUnitDataForTruck($this->truck, 'can');
        } catch (\Throwable $e) {
            $unitId = $this->truck->mapon_unit_id ?? 'null';
            \Log::warning("MaponService getUnitDataForTruck failed unit_id={$unitId}: " . $e->getMessage());
            return null;
        }
    });

    if (!is_array($result)) {
        $this->maponError = 'Не удалось получить данные из Mapon.';
        return;
    }

    $this->maponUnitName = $result['label']
        ?? $result['number']
        ?? ($result['vehicle_title'] ?? null)
        ?? '—';

    $this->maponLastUpdate = $result['last_update'] ?? null;

    // --------------------------------------
    // ODOMETER: CAN → fallback mileage
    // --------------------------------------
    $canValue = data_get($result, 'can.odom.value');
    $canAt    = data_get($result, 'can.odom.gmt');

    if ($canValue !== null && $canValue !== '') {
        // ✅ CAN odometer (км)
        $this->maponCanMileageKm = round((float) $canValue, 1);

        // timestamp CAN, если нет — берём last_update
        $this->maponCanAt = !empty($canAt)
            ? (string) $canAt
            : ($this->maponLastUpdate ? (string) $this->maponLastUpdate : null);

    } else {
        // ✅ CAN нет (Lakna) → используем mileage
        $rawMileage = data_get($result, 'mileage');

        if ($rawMileage === null || $rawMileage === '') {
            // ❌ вот это уже реальная ошибка
            $this->maponError = 'Mapon не вернул ни CAN odometer, ни mileage.';
            return;
        }

        // mileage обычно в метрах → конвертим в км
        $this->maponCanMileageKm = round(((float) $rawMileage) / 1000, 1);

        // timestamp берём last_update
        $this->maponCanAt = $this->maponLastUpdate ? (string) $this->maponLastUpdate : null;
    }

    // --------------------------------------
    // stale logic via config/mapon.php
    // --------------------------------------
    if ($this->maponCanAt) {
        try {
            $now = now();
            $at  = \Carbon\Carbon::parse($this->maponCanAt);

            $this->maponCanDaysAgo = $at->diffInDays($now);

            $thresholdDays    = (int) config('mapon.can_stale_days', 2);
            $thresholdMinutes = (int) config('mapon.can_stale_minutes', 30);

            $isStaleByDays = $thresholdDays > 0 && $at->diffInDays($now) >= $thresholdDays;
            $isStaleByMin  = $thresholdMinutes > 0 && $at->diffInMinutes($now) >= $thresholdMinutes;

            $this->maponCanStale = $isStaleByDays || $isStaleByMin;
        } catch (\Throwable $e) {
            \Log::warning("Mapon time parse failed: {$this->maponCanAt}. " . $e->getMessage());
        }
    }
}




    protected function cacheKey(int|string $unitId): string
    {
        return "mapon:unit:{$unitId}:data:can";
    }

    public function destroy()
    {
        if ($this->truck) {
            $this->truck->delete();
            session()->flash('success', 'Truck deleted successfully.');
            return redirect()->route('trucks.index');
        }

        session()->flash('error', 'Truck not found.');
        return redirect()->route('trucks.index');
    }

    public function render()
    {
        return view('livewire.trucks.show-truck')
            ->layout('layouts.app', [
        'title' => 'Truck info'
    ]);
    }
}
