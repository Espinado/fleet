<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TripDocument;
use App\Enums\TripDocumentType;
use Illuminate\Support\Facades\Storage;

class TripDocumentsSection extends Component
{
    use WithFileUploads;

    public $trip;
    public $types = [];     // ← ВАЖНО, мы добавляем это
    public $type;
    public $name;
    public $documentFile;

    protected $rules = [
        'type'         => 'required|string',
        'name'         => 'required|string|min:3',
        'documentFile' => 'required|file',
    ];

    public function mount()
    {
        // группируем Enum по категориям
        $this->types = collect(TripDocumentType::cases())
            ->groupBy(fn($case) => $case->group())
            ->toArray();

        // дефолтное значение
        $this->type = TripDocumentType::CMR->value;
    }

    public function saveDocument()
    {
        $this->validate();

        $path = $this->documentFile->store("trip_documents/trip_{$this->trip->id}", 'public');

        TripDocument::create([
            'trip_id'     => $this->trip->id,
            'type'        => $this->type,
            'name'        => $this->name,
            'file_path'   => $path,
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);

        $this->reset(['name', 'documentFile']);
        session()->flash('success', '📄 Dokuments veiksmīgi augšupielādēts.');
    }

    public function delete($id)
    {
        $doc = TripDocument::findOrFail($id);

        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
    }

    public function render()
    {
        $documents = TripDocument::where('trip_id', $this->trip->id)
            ->orderBy('uploaded_at', 'desc')
            ->get();

        return view('livewire.trips.trip-documents-section', [
            'documents' => $documents,
            'types'     => $this->types, // ← передаём в Blade
        ]);
    }
}
