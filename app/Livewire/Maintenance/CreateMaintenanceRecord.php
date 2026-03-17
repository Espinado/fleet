<?php

namespace App\Livewire\Maintenance;

use App\Models\Trailer;
use App\Models\Truck;
use App\Models\VehicleMaintenance;
use Livewire\Component;

class CreateMaintenanceRecord extends Component
{
    public ?int $truck_id = null;
    public ?int $trailer_id = null;
    public string $performed_at = '';
    public ?string $odometer_km = null;
    public string $description = '';
    public ?string $cost = null;
    public ?string $next_service_date = null;
    public ?string $next_service_km = null;

    public function mount(?int $truck_id = null, ?int $trailer_id = null): void
    {
        $this->truck_id = $truck_id ?? request()->integer('truck_id') ?: null;
        $this->trailer_id = $trailer_id ?? request()->integer('trailer_id') ?: null;
        $this->performed_at = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'truck_id' => 'required_without:trailer_id|nullable|integer|exists:trucks,id',
            'trailer_id' => 'required_without:truck_id|nullable|integer|exists:trailers,id',
            'performed_at' => 'required|date',
            'odometer_km' => 'nullable|integer|min:0',
            'description' => 'required|string|max:65535',
            'cost' => 'nullable|numeric|min:0',
            'next_service_date' => 'required_without:next_service_km|nullable|date',
            'next_service_km' => 'required_without:next_service_date|nullable|integer|min:0',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'performed_at' => __('app.maintenance_record.performed_at'),
            'odometer_km' => __('app.maintenance_record.odometer_km'),
            'description' => __('app.maintenance_record.description'),
            'cost' => __('app.maintenance_record.cost'),
            'next_service_date' => __('app.maintenance_record.next_service_date'),
        ];
    }

    public function save(): void
    {
        $this->validate($this->rules());

        if (!empty($this->truck_id) && !empty($this->trailer_id)) {
            $this->addError('trailer_id', __('app.maintenance_record.choose_one_vehicle'));
            return;
        }

        $vehicle = $this->truck_id
            ? Truck::find($this->truck_id)
            : Trailer::find($this->trailer_id);

        if (!$vehicle || !$vehicle->company_id) {
            $this->addError('truck_id', __('app.maintenance_record.vehicle_no_company'));
            return;
        }

        VehicleMaintenance::create([
            'company_id' => $vehicle->company_id,
            'truck_id' => $this->truck_id ?: null,
            'trailer_id' => $this->trailer_id ?: null,
            'performed_at' => $this->performed_at,
            'odometer_km' => $this->odometer_km !== null && $this->odometer_km !== '' ? (int) $this->odometer_km : null,
            'description' => $this->description,
            'cost' => $this->cost !== null && $this->cost !== '' ? (float) str_replace(',', '.', $this->cost) : null,
        ]);

        $nextDate = $this->next_service_date && $this->next_service_date !== '' ? $this->next_service_date : null;
        $nextKm = $this->next_service_km !== null && $this->next_service_km !== '' ? (int) $this->next_service_km : null;
        $vehicle->update([
            'next_service_date' => $nextDate,
            'next_service_km' => $nextKm,
        ]);

        session()->flash('message', __('app.maintenance_record.created'));
        $this->redirect(route('maintenance.records.index'), navigate: true);
    }

    public function getTrucksOptionsProperty(): array
    {
        $user = auth()->user();
        $q = Truck::query()->whereHas('company', fn ($c) => $c->where(function ($cc) {
            $cc->where('is_third_party', 0)->orWhereNull('is_third_party');
        }));
        if ($user && !$user->isAdmin() && $user->company_id !== null) {
            $q->where('company_id', $user->company_id);
        }
        return $q->orderBy('plate')->get()->map(fn ($t) => [
            'id' => $t->id,
            'label' => $t->display_name ?? trim($t->brand . ' ' . $t->model . ' ' . $t->plate),
        ])->toArray();
    }

    public function getTrailersOptionsProperty(): array
    {
        $user = auth()->user();
        $q = Trailer::query()->whereHas('company', fn ($c) => $c->where(function ($cc) {
            $cc->where('is_third_party', 0)->orWhereNull('is_third_party');
        }));
        if ($user && !$user->isAdmin() && $user->company_id !== null) {
            $q->where('company_id', $user->company_id);
        }
        return $q->orderBy('plate')->get()->map(fn ($t) => [
            'id' => $t->id,
            'label' => trim(($t->brand ?? '') . ' ' . ($t->plate ?? '')),
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.maintenance.create-maintenance-record')
            ->layout('layouts.app', ['title' => __('app.maintenance_record.create_title')]);
    }
}
