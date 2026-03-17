<?php

namespace App\Livewire\Maintenance;

use App\Models\VehicleMaintenance;
use Livewire\Component;

class EditMaintenanceRecord extends Component
{
    public VehicleMaintenance $record;
    public string $performed_at = '';
    public ?string $odometer_km = null;
    public string $description = '';
    public ?string $cost = null;
    public ?string $next_service_date = null;
    public ?string $next_service_km = null;

    public function mount(VehicleMaintenance $record): void
    {
        $user = auth()->user();
        if ($user && !$user->isAdmin() && $user->company_id !== null && (int) $record->company_id !== (int) $user->company_id) {
            abort(403);
        }
        $this->record = $record;
        $this->performed_at = $record->performed_at->toDateString();
        $this->odometer_km = $record->odometer_km !== null ? (string) $record->odometer_km : null;
        $this->description = $record->description ?? '';
        $this->cost = $record->cost !== null ? (string) $record->cost : null;
        $vehicle = $record->truck_id ? $record->truck : $record->trailer;
        $this->next_service_date = $vehicle && $vehicle->next_service_date ? $vehicle->next_service_date->toDateString() : null;
        $this->next_service_km = $vehicle && $vehicle->next_service_km !== null ? (string) $vehicle->next_service_km : null;
    }

    protected function rules(): array
    {
        return [
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
            'next_service_km' => __('app.maintenance_record.next_service_km'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->record->update([
            'performed_at' => $this->performed_at,
            'odometer_km' => $this->odometer_km !== null && $this->odometer_km !== '' ? (int) $this->odometer_km : null,
            'description' => $this->description,
            'cost' => $this->cost !== null && $this->cost !== '' ? (float) str_replace(',', '.', $this->cost) : null,
        ]);

        $vehicle = $this->record->truck_id ? $this->record->truck : $this->record->trailer;
        if ($vehicle) {
            $nextDate = $this->next_service_date && $this->next_service_date !== '' ? $this->next_service_date : null;
            $nextKm = $this->next_service_km !== null && $this->next_service_km !== '' ? (int) $this->next_service_km : null;
            $vehicle->update(['next_service_date' => $nextDate, 'next_service_km' => $nextKm]);
        }

        session()->flash('message', __('app.maintenance_record.updated'));
        $this->redirect(route('maintenance.records.show', $this->record), navigate: true);
    }

    public function render()
    {
        return view('livewire.maintenance.edit-maintenance-record')
            ->layout('layouts.app', ['title' => __('app.maintenance_record.edit_title')]);
    }
}
