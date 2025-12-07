<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use App\Models\Trip;
use App\Models\TripStep;
use App\Models\TripStepDocument;
use App\Enums\StepDocumentType;

class DriverStepDocumentUploader extends Component
{
    use WithFileUploads;

    public Trip $trip;
    public TripStep $step;

    public string $type;      // Enum value
    public string $comment = '';
    public $file = null;      // ВАЖНО: без типизации

    protected function rules()
    {
        return [
            'file'    => 'required|file|max:51200',
            'comment' => 'nullable|string|max:2000',
            'type'    => ['required', new Enum(StepDocumentType::class)],
        ];
    }

    public function mount(Trip $trip, TripStep $step)
    {
        if (!Auth::user()?->driver) {
            return redirect()->route('driver.login');
        }

        $this->trip = $trip;
        $this->step = $step;

        // безопасный дефолт enum
        $this->type = StepDocumentType::DeliveryNote->value;
    }

    /** ⚡ ПЕРЕИМЕНОВАНО: вместо upload() -> saveDocument() */
    public function saveDocument()
    {
        $this->validate();

        // дополнительная защита от странных фронтенд-глюков
        if (! $this->file) {
            $this->addError('file', 'Lūdzu izvēlieties failu.');
            return;
        }

        $path = $this->file->store("trip_steps/{$this->step->id}", 'public');

        TripStepDocument::create([
            'trip_step_id'       => $this->step->id,
            'trip_id'            => $this->step->trip_id,
            'cargo_id'           => null,
            'uploader_user_id'   => null,
            'uploader_driver_id' => Auth::user()->driver->id ?? null,
            'type'               => $this->type,
            'file_path'          => $path,
            'comment'            => $this->comment,
        ]);

        session()->flash('success', 'Dokuments veiksmīgi augšupielādēts!');

        // обновляем связи и чистим форму
        $this->step->refresh();
        $this->reset(['file', 'comment']);
        $this->type = StepDocumentType::DeliveryNote->value;
    }

    public function render()
    {
        return view('livewire.driver-app.driver-step-document-uploader', [
            'step' => $this->step,
        ]);
    }
}
