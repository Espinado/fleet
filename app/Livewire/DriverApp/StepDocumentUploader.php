<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Trip;
use App\Models\TripStep;
use App\Models\TripStepDocument;
use Illuminate\Support\Facades\Auth;

class StepDocumentUploader extends Component
{
    use WithFileUploads;

    public Trip $trip;
    public TripStep $step;

    public $type = '';
    public $comment = '';
    public $file;

    protected $rules = [
        'file' => 'required|file|max:51200',
        'comment' => 'nullable|string|max:2000',
        'type' => 'nullable|string|max:255',
    ];

    public function mount(Trip $trip, TripStep $step)
    {
        if (!Auth::user()?->driver) {
            return redirect()->route('driver.login');
        }

        $this->trip = $trip;
        $this->step = $step;
    }

    public function upload()
    {
        $this->validate();

        $path = $this->file->store("trip_steps/{$this->step->id}", 'public');

        TripStepDocument::create([
            'step_id' => $this->step->id,
            'type'    => $this->type,
            'comment' => $this->comment,
            'file'    => $path,
        ]);

        session()->flash('success', 'Dokuments veiksmīgi augšupielādēts!');
    }

    public function render()
    {
        return view('livewire.driver-app.step-document-uploader');
    }
}
