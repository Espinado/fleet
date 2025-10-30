<?php

namespace App\Livewire;

use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CmrForm extends Component
{
    // === Основные поля ===
    public string $shipper_name = '';
    public ?string $shipper_address = null;
    public ?string $shipper_country = null;

    public string $consignee_name = '';
    public ?string $consignee_address = null;
    public ?string $consignee_country = null;

    public string $loading_place = '';
    public string $unloading_place = '';
    public ?string $attached_documents = null;
    public string $carrier = '';

    /** @var array<int,array{marks?:string, qty?:int, pack?:string, desc?:string, stat?:string, gross?:float, volume?:float}> */
    public array $items = [
        ['marks' => '', 'qty' => null, 'pack' => '', 'desc' => '', 'stat' => '', 'gross' => null, 'volume' => null],
    ];

    // === Добавить/удалить строку груза ===
    public function addItem(): void
    {
        $this->items[] = ['marks' => '', 'qty' => null, 'pack' => '', 'desc' => '', 'stat' => '', 'gross' => null, 'volume' => null];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // === Валидация ===
    protected function rules(): array
    {
        return [
            'shipper_name'       => 'required|string|max:255',
            'consignee_name'     => 'required|string|max:255',
            'loading_place'      => 'required|string|max:255',
            'unloading_place'    => 'required|string|max:255',
            'carrier'            => 'required|string|max:255',
            'items.*.marks'      => 'nullable|string|max:120',
            'items.*.qty'        => 'nullable|integer|min:0',
            'items.*.pack'       => 'nullable|string|max:60',
            'items.*.desc'       => 'nullable|string|max:300',
            'items.*.stat'       => 'nullable|string|max:40',
            'items.*.gross'      => 'nullable|numeric|min:0',
            'items.*.volume'     => 'nullable|numeric|min:0',
        ];
    }

    // === Генерация PDF и открытие ===
    public function generatePdf(): void
    {
        $this->validate();

        $data = [
            'sender' => [
                'name'     => $this->shipper_name,
                'address'  => $this->shipper_address,
                'country'  => $this->shipper_country,
            ],
            'receiver' => [
                'name'     => $this->consignee_name,
                'address'  => $this->consignee_address,
                'country'  => $this->consignee_country,
            ],
            'loading_place'   => $this->loading_place,
            'unloading_place' => $this->unloading_place,
            'documents'       => $this->attached_documents,
            'carrier'         => ['name' => $this->carrier],
            'items'           => $this->items,
        ];

        // === Создание PDF ===
        $pdf = Pdf::loadView('pdf.cmr-template', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        // === Сохраняем во временную папку ===
        $fileName = 'cmr_' . now()->format('Ymd_His') . '.pdf';
        $dir = 'cmr_temp';
        Storage::disk('public')->makeDirectory($dir);
        $path = "{$dir}/{$fileName}";
        Storage::disk('public')->put($path, $pdf->output());

        // === Отправляем событие для JS ===
        $publicUrl = asset("storage/{$path}");
        $this->dispatchBrowserEvent('open-pdf', ['url' => $publicUrl]);

        session()->flash('success', '✅ CMR successfully generated!');
    }

    public function render()
    {
        return view('livewire.cmr-form');
    }
}
