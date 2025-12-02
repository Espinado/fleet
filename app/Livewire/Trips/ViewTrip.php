<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Trip;
use App\Models\TripStepDocument;
use App\Models\TripCargo;
use App\Http\Controllers\CmrController;

class ViewTrip extends Component
{
    use WithFileUploads;

    public Trip $trip;

    // динамические поля для каждого шага
    public array $stepDocType = [];
    public array $stepDocComment = [];
    public array $stepDocFile = [];

    /* ============================================================
     * UPLOAD DOCUMENT FOR A STEP
     * ============================================================ */
    public function uploadStepDocument(int $stepId)
    {
        $this->validate([
            "stepDocFile.$stepId"    => 'required|file|max:8192|mimes:jpg,jpeg,png,gif,webp,pdf',
            "stepDocType.$stepId"    => 'nullable|string|max:255',
            "stepDocComment.$stepId" => 'nullable|string|max:1000',
        ]);

        $file = $this->stepDocFile[$stepId];

        // Save
        $path = $file->store("trip_steps/$stepId", 'public');

        TripStepDocument::create([
            'trip_step_id'       => $stepId,
            'trip_id'            => $this->trip->id,
            'cargo_id'           => null, // по желанию
            'uploader_user_id'   => auth()->id(),
            'uploader_driver_id' => null,
            'type'               => $this->stepDocType[$stepId] ?? null,
            'file_path'          => $path,
            'original_name'      => $file->getClientOriginalName(),
            'comment'            => $this->stepDocComment[$stepId] ?? null,
        ]);

        // Clear
        unset($this->stepDocFile[$stepId], $this->stepDocComment[$stepId]);

        // Reload only steps + documents (быстрее!)
        $this->trip->load(['cargos.steps.documents']);

        $this->dispatch('stepDocumentUploaded');
    }

    /* ============================================================
     * DELETE DOCUMENT
     * ============================================================ */
    public function deleteStepDocument(int $docId)
    {
        $doc = TripStepDocument::findOrFail($docId);

        \Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        // reload
        $this->trip->load(['cargos.steps.documents']);

        $this->dispatch('stepDocumentDeleted');
    }

    /* ============================================================
     * MOUNT
     * ============================================================ */
    public function mount($trip)
    {
        $this->trip = $trip instanceof Trip
            ? $trip
            : Trip::findOrFail($trip);

        // Загружаем всё сразу (включая documents)
        $this->trip->load([
            'driver',
            'truck',
            'trailer',
            'cargos.shipper',
            'cargos.consignee',
            'cargos.customer',
            'cargos.items',
            'cargos.steps.documents', // ⭐ ВАЖНО
        ]);
    }

    /* ============================================================
     * GENERATE DOCUMENTS
     * ============================================================ */

    public function generateCmr(int $cargoId): void
    {
        $cargo = TripCargo::findOrFail($cargoId);
        $url = app(CmrController::class)->generateAndSave($cargo);

        $this->trip->load(['cargos.items']);

        $this->dispatch('cmrGenerated', url: $url);
    }

    public function generateOrder(int $cargoId)
    {
        $cargo = TripCargo::findOrFail($cargoId);
        $url = app(CmrController::class)->generateTransportOrder($cargo);

        $this->trip->load(['cargos.items']);

        $this->dispatch('orderGenerated', url: $url);
    }

    public function generateInvoice(int $cargoId)
    {
        $cargo = TripCargo::findOrFail($cargoId);
        $url = app(CmrController::class)->generateInvoice($cargo);

        $this->trip->load(['cargos.items']);

        $this->dispatch('invoiceGenerated', url: $url);
    }

    /* ============================================================
     * RENDER
     * ============================================================ */
    public function render()
    {
        return view('livewire.trips.view-trip', [
            'trip' => $this->trip,
        ])
            ->layout('layouts.app')
            ->title('View CMR Trip');
    }
}
