<?php

namespace App\Enums;

enum TripDocumentType: string
{
    /* ==========================
     | STEP LOADING
     ========================== */
    case LoadingBefore     = 'loading_before';
    case LoadingAfter      = 'loading_after';
    case LoadingDocs       = 'loading_docs';
    case LoadingSignature  = 'loading_signature';

    /* ==========================
     | STEP UNLOADING
     ========================== */
    case UnloadingBefore    = 'unloading_before';
    case UnloadingAfter     = 'unloading_after';
    case UnloadingDocs      = 'unloading_docs';
    case UnloadingSignature = 'unloading_signature';

    /* ==========================
     | EXPENSES
     ========================== */
    case FuelReceipt    = 'fuel_receipt';
    case TollReceipt    = 'toll_receipt';
    case ParkingReceipt = 'parking_receipt';
    case OtherExpense   = 'other_expense';

    /* ==========================
     | TRIP DOCUMENTS
     ========================== */
    case CMR            = 'cmr';
    case TransportOrder = 'order';
    case Invoice        = 'invoice';
    case Permit         = 'permit';
    case Insurance      = 'insurance';

    /* ==========================
     | OTHER
     ========================== */
    case Additional = 'additional';

    public function label(): string
    {
        return \Illuminate\Support\Facades\Lang::get('app.enums.trip_document_type.' . $this->value);
    }

    /* =======================================================
       GROUPING (for select Optgroups)
       ======================================================= */
    public function group(): string
    {
        return match ($this) {

            // Step documents
            self::LoadingBefore,
            self::LoadingAfter,
            self::LoadingDocs,
            self::LoadingSignature,
            self::UnloadingBefore,
            self::UnloadingAfter,
            self::UnloadingDocs,
            self::UnloadingSignature
                => 'step',

            // Expense receipts
            self::FuelReceipt,
            self::TollReceipt,
            self::ParkingReceipt,
            self::OtherExpense
                => 'expenses',

            // Trip-level documents
            self::CMR,
            self::TransportOrder,
            self::Invoice,
            self::Permit,
            self::Insurance
                => 'trip',

            // Other
            self::Additional => 'other',
        };
    }

    /* =======================================================
       SAFE PARSER (never throws)
       ======================================================= */
    public static function fromValue(string $value): self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        // fallback (never breaks UI)
        return self::Additional;
    }
}
