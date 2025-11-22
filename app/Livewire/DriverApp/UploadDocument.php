<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;
use App\Models\TripStep;
use App\Models\TripDocument;
use App\Enums\TripDocumentType;

class UploadDocument extends Component
{
    use WithFileUploads;

    public Trip $trip;
    public TripStep $step;
    public TripDocumentType $type;

    public $files = [];
    public bool $uploading = false;

    /**
     * Карта коротких алиасов → enum значений
     */
    protected function resolveType(string $value): string
    {
        return match ($value) {
            'before' => 'loading_before',
            'after'  => 'loading_after',
            'docs'   => 'loading_docs',
            'extra'  => 'additional',
            default  => $value,
        };
    }

    public function mount($trip, $step, $type)
    {
        dd('a');

        \Log::info("UPLOAD MOUNT: trip=$trip, step=$step, type=$type");
        $user = Auth::user();
        if (!$user || !$user->driver) {
            return redirect()->route('driver.login');
        }

        $this->trip = Trip::findOrFail($trip);
        $this->step = TripStep::findOrFail($step);

        // конвертируем короткие алиасы в enum
        $resolved = $this->resolveType($type);

        // превратим в enum (если ошибка → 404)
        $this->type = TripDocumentType::from($resolved);
    }

    public function save()
    {
        $this->validate([
            'files.*' => 'required|image|max:20480', // 20 MB
        ]);

        foreach ($this->files as $file) {
            $path = $file->store('trip-documents', 'public');

            TripDocument::create([
                'trip_id'     => $this->trip->id,
                'step_id'     => $this->step->id,
                'type'        => $this->type,
                'name'        => $this->type->label(),
                'file_path'   => $path,
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now(),
            ]);
        }

        session()->flash('success', 'Файлы загружены!');
        return redirect()->route('driver.trip', $this->trip->id);
    }

    public function render()
    {
    return view('driver-app.pages.upload-document')
        ->layout('driver-app.layouts.app', [
            'title' => $this->type->label(),
            'back'  => true,
        ]);
    }
}
