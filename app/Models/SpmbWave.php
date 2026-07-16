<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpmbWave extends Model
{
    use \App\Traits\HasUuid, HasFactory;

    protected $fillable = [
        'spmb_period_id',
        'name',
        'registration_start',
        'registration_end',
        'announcement_date',
        'quota',
        'registration_fee',
        'status',
    ];

    protected $casts = [
        'registration_start' => 'date',
        'registration_end' => 'date',
        'announcement_date' => 'date',
    ];

    public function period()
    {
        return $this->belongsTo(SpmbPeriod::class, 'spmb_period_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'spmb_wave_id');
    }
}
