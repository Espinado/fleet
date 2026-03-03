<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TripDocument;
use App\Models\TripStepDocument;
use App\Enums\TripDocumentType;
use App\Helpers\ImageCompress;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

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

        $path = ImageCompress::storeUpload($this->documentFile, "trip_documents/trip_{$this->trip->id}", 'public');

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
        $tripDocs = TripDocument::where('trip_id', $this->trip->id)
            ->orderBy('uploaded_at', 'desc')
            ->get();

        $stepDocs = TripStepDocument::where('trip_id', $this->trip->id)
            ->with('step')
            ->orderBy('created_at', 'desc')
            ->get();

        $documents = $this->buildAllDocumentsList($tripDocs, $stepDocs);

        return view('livewire.trips.trip-documents-section', [
            'documents' => $documents,
            'types'     => $this->types,
        ]);
    }

    /**
     * Объединяет документы рейса и документы по шагам в один список с пометкой шага.
     *
     * @return Collection<int, object{type_label: string, name: string, uploaded_at: \Carbon\Carbon|null, file_url: string, step_label: string|null}>
     */
    private function buildAllDocumentsList($tripDocs, $stepDocs): Collection
    {
        $list = collect();

        foreach ($tripDocs as $doc) {
            $list->push((object)[
                'type_label'   => $doc->type?->label() ?? '—',
                'name'         => $doc->name ?? '—',
                'uploaded_at'  => $doc->uploaded_at,
                'file_url'     => $doc->file_url ?? asset('storage/' . $doc->file_path),
                'step_label'   => null,
            ]);
        }

        foreach ($stepDocs as $doc) {
            $step = $doc->step;
            $stepLabel = $step
                ? ('Solis ' . ($step->order ?? $step->id) . ': ' . $step->typeLabel())
                : ('Solis #' . $doc->trip_step_id);

            $list->push((object)[
                'type_label'   => $doc->type?->label() ?? '—',
                'name'         => trim($doc->comment ?? $doc->original_name ?? '') ?: '—',
                'uploaded_at'  => $doc->created_at,
                'file_url'     => asset('storage/' . $doc->file_path),
                'step_label'   => $stepLabel,
            ]);
        }

        return $list->sortByDesc(fn($d) => $d->uploaded_at?->timestamp ?? 0)->values();
    }
}
