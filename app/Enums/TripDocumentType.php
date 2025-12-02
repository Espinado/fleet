<?php

namespace App\Enums;

enum TripDocumentType: string
{
    /* ------------------------------
     |  STEP LOADING
     |------------------------------*/
    case LoadingBefore = 'loading_before';
    case LoadingAfter = 'loading_after';
    case LoadingDocs = 'loading_docs';
    case LoadingSignature = 'loading_signature';

    /* ------------------------------
     |  STEP UNLOADING
     |------------------------------*/
    case UnloadingBefore = 'unloading_before';
    case UnloadingAfter = 'unloading_after';
    case UnloadingDocs = 'unloading_docs';
    case UnloadingSignature = 'unloading_signature';

    /* ------------------------------
     |  STEP EXPENSES
     |------------------------------*/
    case FuelReceipt = 'fuel_receipt';
    case TollReceipt = 'toll_receipt';
    case ParkingReceipt = 'parking_receipt';
    case OtherExpense = 'other_expense';

    /* ------------------------------
     |  TRIP DOCUMENTS (GLOBAL)
     |------------------------------*/
    case CMR = 'cmr';
    case TransportOrder = 'order';
    case Invoice = 'invoice';
    case Permit = 'permit';
    case Insurance = 'insurance';

    /* ------------------------------
     |  OTHER
     |------------------------------*/
    case Additional = 'additional';


    /* LABEL */
    public function label(): string
    {
        return match ($this) {

            // STEP LOADING
            self::LoadingBefore => 'Фото ДО погрузки',
            self::LoadingAfter => 'Фото ПОСЛЕ погрузки',
            self::LoadingDocs => 'Документы погрузки',
            self::LoadingSignature => 'Подпись отправителя',

            // STEP UNLOADING
            self::UnloadingBefore => 'Фото ДО разгрузки',
            self::UnloadingAfter => 'Фото ПОСЛЕ разгрузки',
            self::UnloadingDocs => 'Документы разгрузки',
            self::UnloadingSignature => 'Подпись получателя',

            // EXPENSES
            self::FuelReceipt => 'Чек за топливо',
            self::TollReceipt => 'Платные дороги',
            self::ParkingReceipt => 'Парковка',
            self::OtherExpense => 'Прочий расход',

            // TRIP DOCUMENTS
            self::CMR => 'CMR',
            self::TransportOrder => 'Transporta pasūtījums',
            self::Invoice => 'Rēķins',
            self::Permit => 'Atļauja',
            self::Insurance => 'Apdrošināšana',

            // OTHER
            self::Additional => 'Cits',
        };
    }

    public function group(): string
    {
        return match ($this) {
            // Step docs
            self::LoadingBefore,
            self::LoadingAfter,
            self::LoadingDocs,
            self::LoadingSignature,
            self::UnloadingBefore,
            self::UnloadingAfter,
            self::UnloadingDocs,
            self::UnloadingSignature
                => 'step',

            // Expenses
            self::FuelReceipt,
            self::TollReceipt,
            self::ParkingReceipt,
            self::OtherExpense
                => 'expenses',

            // Trip documents
            self::CMR,
            self::TransportOrder,
            self::Invoice,
            self::Permit,
            self::Insurance
                => 'trip',

            self::Additional => 'other',
        };
    }
}
