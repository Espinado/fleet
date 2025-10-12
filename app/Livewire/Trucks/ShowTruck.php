<?php

namespace App\Livewire\Trucks;

use Livewire\Component;
use App\Models\Truck;


class ShowTruck extends Component
{


    public Truck $truck;

    protected $listeners = ['deleteConfirmed' => 'deleteTruck'];

    public function deleteTruck($id)
    {
          $truck = Truck::find($id);

        if ($truck) {
            $truck->delete();
            session()->flash('message', 'Truck deleted successfully!');
        }

        return redirect()->route('trucks.list');
    }

    public function mount(Truck $truck)
    {
        $this->truck = $truck;
    }

    public function render()
    {
        return view('livewire.trucks.show-truck') ->layout('layouts.app');
    }
}
