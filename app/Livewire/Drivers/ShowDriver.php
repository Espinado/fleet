<?php

namespace App\Livewire\Drivers;

use App\Models\Driver;
use Livewire\Component;

class ShowDriver extends Component
{
    public $driver;

    public function mount(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function render()
    {
       return view('livewire.drivers.show-driver')
        ->layout('layouts.app');
    }
}
