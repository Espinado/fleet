<?php

namespace App\Livewire\Trucks;

use Livewire\Component;
use App\Models\Truck;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\Services\MaponService;

class ShowTruck extends Component
{
    public Truck $truck;

    public ?float $maponMileageKm = null; // км
    public ?string $maponUnitName = null; // label/number
    public ?string $maponError = null;

    protected $listeners = ['deleteConfirmed' => 'deleteTruck'];

    public function mount(Truck $truck)
    {
        $this->truck = $truck;
        $this->loadMaponData();
    }

    public function loadMaponData(): void
    {
        // сброс чтобы не “залипало”
        $this->maponError = null;
        $this->maponMileageKm = null;
        $this->maponUnitName = null;

        $unitId = $this->truck->mapon_unit_id ?? null;

        if (!$unitId) {
            $this->maponError = 'mapon_unit_id не задан для данного трака.';
            return;
        }

        $cacheKey = "mapon:unit:{$unitId}:data";

        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($unitId) {
            try {
                /** @var MaponService $svc */
                $svc = app(MaponService::class);
                return $svc->getUnitData($unitId);
            } catch (\Throwable $e) {
                Log::warning("MaponService getUnitData failed unit_id={$unitId}: " . $e->getMessage());
                return null;
            }
        });

        if (!is_array($result)) {
            $this->maponError = 'Не удалось получить данные из Mapon.';
            return;
        }

        // Mapon отдаёт label/number (у тебя в логе есть "label":"DAF", "number":"JG-7571")
        $this->maponUnitName = $result['label']
            ?? $result['number']
            ?? ($result['vehicle_title'] ?? null)
            ?? '—';

        // mileage приходит огромным числом — это метры → переводим в км
        if (isset($result['mileage'])) {
            $meters = (float) $result['mileage'];
            $this->maponMileageKm = $meters > 0 ? round($meters / 1000, 0) : 0.0;
        } else {
            $this->maponError = 'Mapon не вернул поле mileage для этого unit.';
        }
    }

    public function deleteTruck($id)
    {
        $truck = Truck::find($id);

        if ($truck) {
            $truck->delete();
            session()->flash('message', 'Truck deleted successfully!');
        }

        return redirect()->route('trucks.list');
    }

    public function destroy()
    {
        if ($this->truck) {
            $this->truck->delete();
            $this->reset();
            session()->flash('success', 'Truck deleted successfully.');
            return redirect()->route('trucks.index');
        }

        session()->flash('error', 'Truck not found.');
    }

    public function render()
    {
        return view('livewire.trucks.show-truck')->layout('layouts.app');
    }
}
