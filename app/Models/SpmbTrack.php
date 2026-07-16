<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpmbTrack extends Model
{
    use \App\Traits\HasUuid, HasFactory;

    protected $fillable = [
        'spmb_period_id',
        'name',
        'quota',
        'scoring_weights',
        'requirements',
    ];

    protected $casts = [
        'scoring_weights' => 'array',
    ];

    public function period()
    {
        return $this->belongsTo(SpmbPeriod::class, 'spmb_period_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'spmb_track_id');
    }
}
