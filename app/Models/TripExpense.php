<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TripExpenseCategory;

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
        'category' => TripExpenseCategory::class,
        'expense_date' => 'date',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getLabelAttribute(): string
    {
        return $this->category->label();
    }
}
