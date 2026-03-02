<?php

namespace App\Enums;

use Illuminate\Support\Facades\Lang;

enum StepDocumentType: string
{
    case DeliveryNote   = 'delivery_note';
    case WarehouseStamp = 'warehouse_stamp';
    case PalletList     = 'pallet_list';
    case Invoice        = 'invoice';
    case Other          = 'other';

    public function label(): string
    {
        return Lang::get('app.enums.step_document_type.' . $this->value);
    }

    /* --------------------------------
     |  OPTIONAL: ICONS
     |--------------------------------*/
    public function icon(): string
    {
        return match ($this) {
            self::DeliveryNote => '📄',
            self::WarehouseStamp => '🏷️',
            self::PalletList => '📦',
            self::Invoice => '💶',
            self::Other => '📎',
        };
    }
}
