<?php

namespace App\Livewire\Carriers;

use App\Models\Company;
use Livewire\Component;

class ShowCarrier extends Component
{
    public Company $carrier;

    public function mount(Company $carrier): void
    {
        $this->carrier = $carrier;
        if ($this->carrier->type !== 'carrier' || !$this->carrier->is_third_party) {
            abort(404);
        }
        $this->carrier->loadCount('trips');
    }

    public function render()
    {
        return view('livewire.carriers.show-carrier', [
            'trips' => $this->carrier->trips()->latest('start_date')->paginate(10),
        ])->layout('layouts.app', [
            'title' => $this->carrier->name . ' — ' . __('app.carriers.title'),
        ]);
    }
}
