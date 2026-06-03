<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTime extends Model
{
    protected $fillable = [
        'schedule_preset_id',
        'session_number',
        'start_time',
        'end_time',
    ];

    public function preset()
    {
        return $this->belongsTo(SchedulePreset::class, 'schedule_preset_id');
    }
}
