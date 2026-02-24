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

    // step docs
    public array $stepDocType = [];
    public array $stepDocComment = [];
    public array $stepDocFile = [];

    // ✅ ручные номера документов по cargo_id
    public array $cmrNr = [];   // cargo_id => string
    public array $orderNr = []; // cargo_id => string
    public array $invNr = [];   // cargo_id => string

    public function mount($trip)
    {
        $this->trip = $trip instanceof Trip ? $trip : Trip::findOrFail($trip);

        $this->reloadTrip();

        // заполнить поля ввода номеров из БД
        foreach ($this->trip->cargos as $cargo) {
            $cid = (int) $cargo->id;
            $this->cmrNr[$cid]   = (string)($cargo->cmr_nr ?? '');
            $this->orderNr[$cid] = (string)($cargo->order_nr ?? '');
            $this->invNr[$cid]   = (string)($cargo->inv_nr ?? '');
        }
    }

    private function reloadTrip(): void
    {
        $this->trip->load([
            'driver',
            'truck',
            'trailer',

            'cargos.shipper',
            'cargos.consignee',
            'cargos.customer',
            'cargos.items',
            'cargos.steps.documents',

            'steps.cargos',
        ]);
    }

    public function uploadStepDocument(int $stepId)
    {
        $this->validate([
            "stepDocFile.$stepId"    => 'required|file|max:8192|mimes:jpg,jpeg,png,gif,webp,pdf',
            "stepDocType.$stepId"    => 'nullable|string|max:255',
            "stepDocComment.$stepId" => 'nullable|string|max:1000',
        ]);

        $file = $this->stepDocFile[$stepId];
        $path = $file->store("trip_steps/$stepId", 'public');

        TripStepDocument::create([
            'trip_step_id'       => $stepId,
            'trip_id'            => $this->trip->id,
            'cargo_id'           => null,
            'uploader_user_id'   => auth()->id(),
            'uploader_driver_id' => null,
            'type'               => $this->stepDocType[$stepId] ?? null,
            'file_path'          => $path,
            'original_name'      => $file->getClientOriginalName(),
            'comment'            => $this->stepDocComment[$stepId] ?? null,
        ]);

        unset($this->stepDocFile[$stepId], $this->stepDocComment[$stepId]);

        $this->trip->load(['cargos.steps.documents']);
        $this->dispatch('stepDocumentUploaded');
    }

    public function deleteStepDocument(int $docId)
    {
        $doc = TripStepDocument::findOrFail($docId);

        \Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        $this->trip->load(['cargos.steps.documents']);
        $this->dispatch('stepDocumentDeleted');
    }

    private function validateDocNr(string $field, int $cargoId): string
    {
        $this->validate([
            "{$field}.{$cargoId}" => 'required|string|max:191',
        ], [
            "{$field}.{$cargoId}.required" => 'Введите номер документа перед генерацией.',
        ]);

        $val = trim((string) data_get($this->{$field}, $cargoId, ''));

        if ($val === '') {
            $this->addError("{$field}.{$cargoId}", 'Введите номер документа перед генерацией.');
        }

        return $val;
    }

    public function generateCmr(int $cargoId): void
    {
        $nr = $this->validateDocNr('cmrNr', $cargoId);

        $cargo = TripCargo::findOrFail($cargoId);
        $cargo->update([
            'cmr_nr' => $nr, // ✅ только cmr_nr
        ]);

        $url = app(CmrController::class)->generateAndSave($cargo);

        $this->reloadTrip();
        $this->dispatch('cmrGenerated', url: $url);
    }

    public function generateOrder(int $cargoId): void
    {
        $nr = $this->validateDocNr('orderNr', $cargoId);

        $cargo = TripCargo::findOrFail($cargoId);
        $cargo->update([
            'order_nr' => $nr,
        ]);

        $url = app(CmrController::class)->generateTransportOrder($cargo);

        $this->reloadTrip();
        $this->dispatch('orderGenerated', url: $url);
    }

    public function generateInvoice(int $cargoId): void
    {
        $nr = $this->validateDocNr('invNr', $cargoId);

        $cargo = TripCargo::findOrFail($cargoId);
        $cargo->update([
            'inv_nr' => $nr, // ✅ только inv_nr
        ]);

        $url = app(CmrController::class)->generateInvoice($cargo);

        $this->reloadTrip();
        $this->dispatch('invoiceGenerated', url: $url);
    }

    public function render()
    {
        return view('livewire.trips.view-trip', [
            'trip' => $this->trip,
        ])
            ->layout('layouts.app')
            ->title('View CMR Trip');
    }
}
