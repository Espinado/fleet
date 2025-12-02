<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\StepDocumentType;
class TripStepDocument extends Model
{

  

protected $casts = [
    'type' => StepDocumentType::class,
];
    protected $fillable = [
        'trip_step_id',
        'trip_id',
        'cargo_id',
        'uploader_user_id',
        'uploader_driver_id',
        'type',
        'file_path',
        'original_name',
        'comment',
    ];

    public function step()
    {
        return $this->belongsTo(TripStep::class, 'trip_step_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function cargo()
    {
        return $this->belongsTo(TripCargo::class);
    }
}
