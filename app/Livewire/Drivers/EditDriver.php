<?php

namespace App\Livewire\Drivers;

use Livewire\Component;
use App\Models\Driver;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EditDriver extends Component
{
    use WithFileUploads;

    public Driver $driver;

    // Свойства для новых файлов
    public $photo;
    public $license_photo;
    public $medical_certificate_photo;

    // Инициализация с текущими данными водителя
    public function mount(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function save()
    {
        // Загрузка новых фото, если они выбраны
        if ($this->photo) {
            if ($this->driver->photo) {
                Storage::delete($this->driver->photo);
            }
            $this->driver->photo = $this->photo->store('drivers');
        }

        if ($this->license_photo) {
            if ($this->driver->license_photo) {
                Storage::delete($this->driver->license_photo);
            }
            $this->driver->license_photo = $this->license_photo->store('drivers');
        }

        if ($this->medical_certificate_photo) {
            if ($this->driver->medical_certificate_photo) {
                Storage::delete($this->driver->medical_certificate_photo);
            }
            $this->driver->medical_certificate_photo = $this->medical_certificate_photo->store('drivers');
        }

        $this->driver->save();

        session()->flash('message', 'Driver updated successfully.');
    }

    public function render()
    {
        return view('livewire.drivers.edit')
        ->layout('layouts.app');
    }
}
