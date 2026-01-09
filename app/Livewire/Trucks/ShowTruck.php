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

        $cacheKey = $this->cacheKey($unitId);

        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($unitId) {
            try {
                /** @var MaponService $svc */
                $svc = app(MaponService::class);

                // include=can
                return $svc->getUnitData($unitId, 'can');
            } catch (\Throwable $e) {
                Log::warning("MaponService getUnitData failed unit_id={$unitId}: " . $e->getMessage());
                return null;
            }
        });

        if (!is_array($result)) {
            $this->maponError = 'Не удалось получить данные из Mapon.';
            return;
        }

        // debug only in local
        if (app()->isLocal()) {
            Log::info('Mapon unit payload (with CAN)', [
                'unit_id' => $unitId,
                'can' => $result['can'] ?? null,
                'last_update' => $result['last_update'] ?? null,
            ]);
        }

        $this->maponUnitName = $result['label']
            ?? $result['number']
            ?? ($result['vehicle_title'] ?? null)
            ?? '—';

        $this->maponLastUpdate = $result['last_update'] ?? null;

        // ✅ CAN odometer path for your payload: can.odom.value (km)
        $canValue = data_get($result, 'can.odom.value');
        $canAt    = data_get($result, 'can.odom.gmt');

        if ($canValue === null || $canValue === '') {
            $this->maponError = 'Mapon не вернул CAN odometer (can.odom.value).';
            return;
        }

        $this->maponCanMileageKm = round((float) $canValue, 1);
        $this->maponCanAt = $canAt ?: null;

        // ✅ stale logic via config/mapon.php => can_stale_days
        if ($this->maponCanAt) {
            $days = Carbon::parse($this->maponCanAt)->diffInDays(now());
             $this->maponCanDaysAgo = (int) $days;

            $threshold = (int) config('mapon.can_stale_days', 2);
            $this->maponCanStale = $days >= $threshold;
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
            ->layout('layouts.app');
    }
}
