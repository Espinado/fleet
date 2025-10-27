<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CmrForm extends Component
{
    // 1–5–16–… ключевые поля
    #[Validate('required|string|max:255')] public string $shipper_name = '';
    #[Validate('nullable|string|max:255')] public ?string $shipper_address = null;
    #[Validate('nullable|string|max:255')] public ?string $shipper_country = null;

    #[Validate('required|string|max:255')] public string $consignee_name = '';
    #[Validate('nullable|string|max:255')] public ?string $consignee_address = null;
    #[Validate('nullable|string|max:255')] public ?string $consignee_country = null;

    #[Validate('required|string|max:255')] public string $loading_place = '';
    #[Validate('required|string|max:255')] public string $unloading_place = '';

    #[Validate('nullable|string|max:500')] public ?string $attached_documents = null;

    #[Validate('required|string|max:255')] public string $carrier = '';

    // Таблица 6–12 (строки груза)
    /** @var array<int,array{marks?:string, qty?:int, pack?:string, desc?:string, stat?:string, gross?:float, volume?:float}> */
    public array $items = [
        ['marks' => '', 'qty' => null, 'pack' => '', 'desc' => '', 'stat' => '', 'gross' => null, 'volume' => null],
    ];

    public function addItem(): void
    {
        $this->items[] = ['marks' => '', 'qty' => null, 'pack' => '', 'desc' => '', 'stat' => '', 'gross' => null, 'volume' => null];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function rules(): array
    {
        return [
            'items.*.marks'  => 'nullable|string|max:120',
            'items.*.qty'    => 'nullable|integer|min:0',
            'items.*.pack'   => 'nullable|string|max:60',
            'items.*.desc'   => 'nullable|string|max:300',
            'items.*.stat'   => 'nullable|string|max:40',
            'items.*.gross'  => 'nullable|numeric|min:0',
            'items.*.volume' => 'nullable|numeric|min:0',
        ];
    }

    public function generatePdf(): Response
    {
        $this->validate();

        $data = [
            'sender'          => trim($this->shipper_name . ', ' . ($this->shipper_address ?? '') . ($this->shipper_country ? ', ' . $this->shipper_country : '')),
            'receiver'        => trim($this->consignee_name . ', ' . ($this->consignee_address ?? '') . ($this->consignee_country ? ', ' . $this->consignee_country : '')),
            'loading_place'   => $this->loading_place,
            'unloading_place' => $this->unloading_place,
            'documents'       => $this->attached_documents,
            'carrier'         => $this->carrier,
            'items'           => $this->items,
        ];

        $pdf = Pdf::loadView('pdf.cmr-template', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 96,
            ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'cmr.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        return view('livewire.cmr-form');
    }
}
