<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafetyDrill extends Model
{
    use HasFactory;

    protected $fillable = [
        'drill_type', 'scheduled_date', 'start_time', 'end_time',
        'evacuation_time_seconds', 'headcount_unaccounted', 'observer_notes',
        'compliance_status'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];
}
