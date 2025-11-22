<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'type',
        'name',
        'file_path',
        'uploaded_by',
        'uploaded_at',
        'step_id',
    ];

    protected $casts = [
    'uploaded_at' => 'datetime',
     'type' => \App\Enums\TripDocumentType::class,
    'uploaded_at' => 'datetime',
];

    protected $dates = ['uploaded_at'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function step()
{
    return $this->belongsTo(TripStep::class);
}
}
