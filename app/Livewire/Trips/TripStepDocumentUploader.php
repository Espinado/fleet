<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use App\Models\TripStep;
use App\Models\TripStepDocument;
use App\Enums\StepDocumentType;

class TripStepDocumentUploader extends Component
{
    use WithFileUploads;

    public TripStep $step;

    public string $type;
    public string $comment = '';
    public $file = null;

    protected function rules()
    {
        return [
            'type'    => ['required', new Enum(StepDocumentType::class)],
            'comment' => 'nullable|string|max:2000',
            'file'    => 'required|file|max:51200',
        ];
    }

    public function mount(TripStep $step)
    {
        $this->step = $step;

        // Устанавливаем безопасный дефолт
        $this->type = StepDocumentType::DeliveryNote->value;
    }

    public function saveDocument()
    {
        $this->validate();

        $path = $this->file->store("trip_steps/{$this->step->id}", 'public');

        TripStepDocument::create([
            'trip_step_id'       => $this->step->id,
            'trip_id'            => $this->step->trip_id,
            'cargo_id'           => null, // Step не хранит cargo_id — и это правильно
            'uploader_user_id'   => Auth::id(),
            'uploader_driver_id' => null,
            'type'               => $this->type,
            'file_path'          => $path,
            'comment'            => $this->comment,
        ]);

        // Обновляем модель
        $this->step->refresh();

        // Обновляем таблицу на фронте
        $this->dispatch('stepDocumentUploaded');

        // Сбрасываем форму — и снова ставим корректный enum
        $this->reset(['file', 'comment']);
        $this->type = StepDocumentType::DeliveryNote->value;

        session()->flash('success', 'Dokuments veiksmīgi augšupielādēts!');
    }

    public function delete($id)
    {
        $doc = TripStepDocument::findOrFail($id);

        if (\Storage::disk('public')->exists($doc->file_path)) {
            \Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        $this->dispatch('stepDocumentDeleted');

        session()->flash('success', 'Dokuments dzēsts!');
    }

    public function render()
    {
        return view('livewire.trips.trip-step-document-uploader', [
            'documents' => $this->step->stepDocuments()->latest()->get(),
        ]);
    }
}
