<?php

namespace App\Livewire\Maintenance;

use App\Models\VehicleMaintenance;
use Livewire\Component;

class ShowMaintenanceRecord extends Component
{
    public VehicleMaintenance $record;

    public function mount(VehicleMaintenance $record): void
    {
        $user = auth()->user();
        if ($user && !$user->isAdmin() && $user->company_id !== null && (int) $record->company_id !== (int) $user->company_id) {
            abort(403);
        }
        $this->record = $record;
    }

    public function render()
    {
        return view('livewire.maintenance.show-maintenance-record')
            ->layout('layouts.app', ['title' => __('app.maintenance_record.show_title')]);
    }
}
