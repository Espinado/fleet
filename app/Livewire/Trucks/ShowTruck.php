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

       public function destroy()
{
    if ($this->truck) {
        $this->truck->delete();

        // Можно сбросить поля формы, если остаёмся на этой странице
        $this->reset();

        // Сообщение пользователю
        session()->flash('success', 'Truck deleted successfully.');

        // При желании — редирект на список водителей
        return redirect()->route('trucks.index');
    }

    session()->flash('error', 'Truck not found.');
}
}
