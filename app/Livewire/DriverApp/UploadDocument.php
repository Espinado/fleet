<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;

class UploadDocument extends Component
{
    use WithFileUploads;

    public $trip;
    public $step;
    public $type;

    public $file;

    public function mount($trip, $step, $type)
    {
        $this->trip = $trip;
        $this->step = $step;
        $this->type = $type;
    }

    public function upload()
    {
        $this->validate([
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        // сохраняем файл
        $path = $this->file->store("driver_docs/trip_{$this->trip}/{$this->type}", 'public');

        session()->flash('success', 'Документ успешно загружен!');

        // После успешной загрузки сбросим input
        $this->reset('file');
    }

    public function render()
    {
        return view('livewire.driver-app.upload-document');
    }
}
