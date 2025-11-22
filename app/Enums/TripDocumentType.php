<?php

namespace App\Enums;

enum TripDocumentType: string
{
    /* --------------------------------
     |  ПОГРУЗКА
     |--------------------------------*/
    case LoadingBefore = 'loading_before';          
    case LoadingAfter = 'loading_after';            
    case LoadingDocs = 'loading_docs';              
    case LoadingSignature = 'loading_signature';    

    /* --------------------------------
     |  РАЗГРУЗКА
     |--------------------------------*/
    case UnloadingBefore = 'unloading_before';      
    case UnloadingAfter = 'unloading_after';        
    case UnloadingDocs = 'unloading_docs';          
    case UnloadingSignature = 'unloading_signature';

    /* --------------------------------
     |  РАСХОДЫ
     |--------------------------------*/
    case FuelReceipt = 'fuel_receipt';              
    case TollReceipt = 'toll_receipt';              
    case ParkingReceipt = 'parking_receipt';        
    case OtherExpense = 'other_expense';            

    /* --------------------------------
     |  ДОПОЛНИТЕЛЬНО
     |--------------------------------*/
    case Additional = 'additional';                  


    /* --------------------------------
     |  LABEL (человекочитаемо)
     |--------------------------------*/
    public function label(): string
    {
        return match ($this) {
            // Loading
            self::LoadingBefore => 'Фото ДО погрузки',
            self::LoadingAfter => 'Фото ПОСЛЕ погрузки',
            self::LoadingDocs => 'Документы погрузки',
            self::LoadingSignature => 'Подпись отправителя',

            // Unloading
            self::UnloadingBefore => 'Фото ДО разгрузки',
            self::UnloadingAfter => 'Фото ПОСЛЕ разгрузки',
            self::UnloadingDocs => 'Документы разгрузки',
            self::UnloadingSignature => 'Подпись получателя',

            // Expenses
            self::FuelReceipt => 'Чек за топливо',
            self::TollReceipt => 'Платные дороги',
            self::ParkingReceipt => 'Парковка',
            self::OtherExpense => 'Прочий расход',

            // Other
            self::Additional => 'Дополнительный документ',
        };
    }


    /* --------------------------------
     |  GROUP — определяет раздел UI
     |--------------------------------*/
    public function group(): string
    {
        return match ($this) {
            self::LoadingBefore,
            self::LoadingAfter,
            self::LoadingDocs,
            self::LoadingSignature
                => 'loading',

            self::UnloadingBefore,
            self::UnloadingAfter,
            self::UnloadingDocs,
            self::UnloadingSignature
                => 'unloading',

            self::FuelReceipt,
            self::TollReceipt,
            self::ParkingReceipt,
            self::OtherExpense
                => 'expenses',

            self::Additional => 'other',
        };
    }


    /* --------------------------------
     |  ICON — emoji для UI
     |--------------------------------*/
    public function icon(): string
    {
        return match ($this) {
            // Loading
            self::LoadingBefore => '📸',
            self::LoadingAfter => '📸',
            self::LoadingDocs => '📄',
            self::LoadingSignature => '✍️',

            // Unloading
            self::UnloadingBefore => '📸',
            self::UnloadingAfter => '📸',
            self::UnloadingDocs => '📄',
            self::UnloadingSignature => '✍️',

            // Expenses
            self::FuelReceipt => '⛽',
            self::TollReceipt => '🛣️',
            self::ParkingReceipt => '🅿️',
            self::OtherExpense => '💸',

            // Other
            self::Additional => '📎',
        };
    }


    /* --------------------------------
     |  COLOR — Tailwind цвета
     |--------------------------------*/
    public function color(): string
    {
        return match ($this) {
            // Loading
            self::LoadingBefore,
            self::LoadingAfter,
            self::LoadingDocs,
            self::LoadingSignature
                => 'blue',

            // Unloading
            self::UnloadingBefore,
            self::UnloadingAfter,
            self::UnloadingDocs,
            self::UnloadingSignature
                => 'green',

            // Expenses
            self::FuelReceipt,
            self::TollReceipt,
            self::ParkingReceipt,
            self::OtherExpense
                => 'yellow',

            // Other
            self::Additional => 'gray',
        };
    }
}
