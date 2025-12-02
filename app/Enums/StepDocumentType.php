<?php

namespace App\Enums;

enum StepDocumentType: string
{
    /* --------------------------------
     |  STEP DOCUMENTS
     |--------------------------------*/
    case DeliveryNote = 'delivery_note';       // Pavadzīme
    case WarehouseStamp = 'warehouse_stamp';   // Noliktavas zīmogs
    case PalletList = 'pallet_list';           // Palešu saraksts
    case Invoice = 'invoice';                  // Rēķins
    case Other = 'other';                      // Cits

    /* --------------------------------
     |  OPTIONAL: LABELS FOR UI
     |--------------------------------*/
    public function label(): string
    {
        return match ($this) {
            self::DeliveryNote => 'Pavadzīme',
            self::WarehouseStamp => 'Noliktavas zīmogs',
            self::PalletList => 'Palešu saraksts',
            self::Invoice => 'Rēķins',
            self::Other => 'Cits',
        };
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
