<?php

namespace App\Livewire\Trips;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\Trip;
use App\Models\TripStepDocument;
use App\Models\TripCargo;
use App\Models\Invoice;
use App\Http\Controllers\CmrController;
use App\Helpers\CalculateTax;

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

    // ✅ Dikstāve (delay) per cargo
    public array $delayChecked = []; // cargo_id => bool
    public array $delayDays = [];    // cargo_id => int|string
    public array $delayAmount = [];  // cargo_id => float|string (without VAT)

    public function mount($trip)
    {
        $this->trip = $trip instanceof Trip ? $trip : Trip::findOrFail($trip);

        $this->reloadTrip();

        // заполнить поля ввода номеров и delay из БД
        foreach ($this->trip->cargos as $cargo) {
            $cid = (int) $cargo->id;
            $this->cmrNr[$cid]   = (string)($cargo->cmr_nr ?? '');
            $this->orderNr[$cid] = (string)($cargo->order_nr ?? '');
            $this->invNr[$cid]   = (string)($cargo->inv_nr ?? '');
            $this->delayChecked[$cid] = (bool) ($cargo->has_delay ?? false);
            $this->delayDays[$cid]    = $cargo->delay_days !== null ? (string) $cargo->delay_days : '';
            $this->delayAmount[$cid]  = $cargo->delay_amount !== null ? (string) $cargo->delay_amount : '';
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
            "stepDocFile.$stepId"    => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf',
            "stepDocType.$stepId"    => 'nullable|string|max:255',
            "stepDocComment.$stepId" => 'nullable|string|max:1000',
        ]);

        $file = $this->stepDocFile[$stepId];
        $path = \App\Helpers\ImageCompress::storeUpload($file, "trip_steps/$stepId", 'public');

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
            "{$field}.{$cargoId}.required" => __('app.trip.show.doc_nr_required'),
        ]);

        $val = trim((string) data_get($this->{$field}, $cargoId, ''));

        if ($val === '') {
            $this->addError("{$field}.{$cargoId}", __('app.trip.show.doc_nr_required'));
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

    /**
     * Save delay (Dikstāve) for a cargo. Saves only when checkbox is checked and days/amount are filled.
     * When checkbox is unchecked: updates DB only if there was saved delay data to clear.
     */
    public function saveDelay(int $cargoId): void
    {
        $checked = (bool) ($this->delayChecked[$cargoId] ?? false);

        $cargo = TripCargo::findOrFail($cargoId);
        if ((int) $cargo->trip_id !== (int) $this->trip->id) {
            abort(403);
        }

        if ($checked) {
            $this->validate([
                "delayDays.{$cargoId}"   => 'required|integer|min:1|max:365',
                "delayAmount.{$cargoId}" => 'required|numeric|min:0',
            ], [
                "delayDays.{$cargoId}.required"   => __('app.trip.show.delay_days_required'),
                "delayAmount.{$cargoId}.required" => __('app.trip.show.delay_amount_required'),
            ]);
            $days = (int) $this->delayDays[$cargoId];
            $amount = (float) str_replace(',', '.', (string) $this->delayAmount[$cargoId]);
        } else {
            // Сняли галочку: сохраняем в БД только если был сохранённый простой (есть что очищать)
            $hadDelay = (bool) ($cargo->has_delay ?? false)
                || $cargo->delay_days !== null
                || $cargo->delay_amount !== null;
            if (!$hadDelay) {
                return; // Ничего не было сохранено — не пишем в БД и не показываем тост
            }
            $days = null;
            $amount = null;
        }

        $cargo->update([
            'has_delay'    => $checked,
            'delay_days'   => $days,
            'delay_amount' => $amount,
            // Инвалидация инвойса: после изменения простоя PDF устарел — нужна перегенерация
            'inv_file'       => null,
            'inv_created_at' => null,
        ]);
        Invoice::where('trip_cargo_id', $cargo->id)->update(['pdf_file' => null]);

        $this->reloadTrip();
        $this->dispatch($checked ? 'delaySaved' : 'delayRemoved');
    }

    /**
     * Удалить Dikstāve по грузу: очистить данные и инвалидировать инвойс.
     */
    public function removeDelay(int $cargoId): void
    {
        try {
            $cargo = TripCargo::findOrFail($cargoId);
            if ((int) $cargo->trip_id !== (int) $this->trip->id) {
                abort(403);
            }

            $cargo->update([
                'has_delay'       => false,
                'delay_days'      => null,
                'delay_amount'    => null,
                'inv_file'        => null,
                'inv_created_at'  => null,
            ]);
            Invoice::where('trip_cargo_id', $cargo->id)->update(['pdf_file' => null]);

            $this->delayChecked[$cargoId] = false;
            $this->delayDays[$cargoId]    = '';
            $this->delayAmount[$cargoId]  = '';
            $this->reloadTrip();
            $this->dispatch('delayRemoved');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('delayRemoveError');
        }
    }

    public function render()
    {
        return view('livewire.trips.view-trip', [
            'trip' => $this->trip,
        ])
            ->layout('layouts.app', [
            'title' => __('app.trip.show.title'),
        ]);
    }
}
