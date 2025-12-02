<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TripStep;
use App\Models\TripStepDocument;
use Illuminate\Support\Facades\Auth;

class TripStepDocumentUploader extends Component
{
    use WithFileUploads;

    public TripStep $step;

    public $type = '';
    public $comment = '';
    public $file = null; // ← ВАЖНО: не upload

    protected $rules = [
        'type'    => 'nullable|string|max:255',
        'comment' => 'nullable|string|max:2000',
        'file'    => 'required|file|max:51200', // 50 MB
    ];

    public function mount(TripStep $step)
    {
        $this->step = $step;
    }

   public function saveDocument()
{
    $this->validate();

    $path = $this->file->store("trip_steps/{$this->step->id}", 'public');

    TripStepDocument::create([
        'trip_step_id'      => $this->step->id,
        'trip_id'           => $this->step->trip_id,
        'cargo_id'          => $this->step->cargo_id,
        'uploader_user_id'  => Auth::id(),
        'uploader_driver_id'=> null,
        'type'              => $this->type,
        'file_path'         => $path,
        'comment'           => $this->comment,
    ]);

    // 🔥 вот то, чего не было:
    $this->step->refresh();
    $this->dispatch('$refresh');

    $this->reset(['file', 'type', 'comment']);

    session()->flash('success', 'Dokuments veiksmīgi augšupielādēts!');
}


    public function delete($id)
    {
        $doc = TripStepDocument::findOrFail($id);

        if (\Storage::disk('public')->exists($doc->file_path)) {
            \Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        session()->flash('success', 'Dokuments dzēsts!');
    }

    public function render()
{
    return view('livewire.trips.trip-step-document-uploader', [
        'documents' => $this->step->stepDocuments()->latest()->get(),
    ]);
}
}
