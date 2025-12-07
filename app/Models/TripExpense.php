<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TripExpenseCategory;
use Illuminate\Support\Facades\Storage;

class TripExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'category',
        'description',
        'amount',
        'currency',
        'file_path',
        'expense_date',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'category'     => TripExpenseCategory::class, // OK
    ];

    /** ⭐ важнейший фикс — не даём enum'у сломать модель */
    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = trim((string)$value);
    }

    /** URL к файлу */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    /** Удобный label */
    public function getLabelAttribute(): string
    {
        return $this->category->label();
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
