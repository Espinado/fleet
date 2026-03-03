<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\TripExpenseCategory;

class TripExpense extends Model
{
    use HasFactory;

    protected $table = 'trip_expenses';

    protected $fillable = [
        'trip_id',
        'trip_cargo_id',
        'supplier_company_id',
        'category',
        'description',
        'overload_note',
        'amount',
        'currency',
        'file_path',
        'expense_date',
        'created_by',
        'liters',

        // ✅ NEW: fuel snapshot + 1:1 link
        'odometer_km',
        'odometer_source',
        'truck_odometer_event_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'odometer_km' => 'decimal:1',
        'liters' => 'decimal:2',

        // если category у тебя Enum — удобно кастить
        'category' => TripExpenseCategory::class,
    ];

    /** ================= Relations ================= */

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripCargo(): BelongsTo
    {
        return $this->belongsTo(TripCargo::class, 'trip_cargo_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function truckOdometerEvent(): BelongsTo
    {
        return $this->belongsTo(TruckOdometerEvent::class, 'truck_odometer_event_id');
    }

    /** ================= Accessors ================= */

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return \Storage::disk('public')->url($this->file_path);
    }
}
