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
        'supplier_company_id',
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

    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = trim((string)$value);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    public function getLabelAttribute(): string
    {
        // защита, если вдруг category не распарсилась в enum
        if ($this->category instanceof TripExpenseCategory) {
            return $this->category->label();
        }

        return (string) $this->category;
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function supplierCompany()
    {
        return $this->belongsTo(Company::class, 'supplier_company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
