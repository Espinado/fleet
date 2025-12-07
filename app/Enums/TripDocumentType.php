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

    /* =======================================================
       LABELS FOR UI
       ======================================================= */
    public function label(): string
    {
        return match ($this) {

            // ---- LOADING ----
            self::LoadingBefore     => 'Foto pirms iekraušanas',
            self::LoadingAfter      => 'Foto pēc iekraušanas',
            self::LoadingDocs       => 'Iekraušanas dokumenti',
            self::LoadingSignature  => 'Nosūtītāja paraksts',

            // ---- UNLOADING ----
            self::UnloadingBefore    => 'Foto pirms izkraušanas',
            self::UnloadingAfter     => 'Foto pēc izkraušanas',
            self::UnloadingDocs      => 'Izkraušanas dokumenti',
            self::UnloadingSignature => 'Saņēmēja paraksts',

            // ---- EXPENSES ----
            self::FuelReceipt    => 'Degvielas čeks',
            self::TollReceipt    => 'Ceļu nodevas',
            self::ParkingReceipt => 'Stāvvieta',
            self::OtherExpense   => 'Cits izdevums',

            // ---- TRIP DOCS ----
            self::CMR            => 'CMR',
            self::TransportOrder => 'Transporta pasūtījums',
            self::Invoice        => 'Rēķins',
            self::Permit         => 'Atļauja',
            self::Insurance      => 'Apdrošināšana',

            // ---- OTHER ----
            self::Additional => 'Cits',
        };
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
