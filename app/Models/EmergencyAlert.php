<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'trigger_source', 'initiated_by_user_id', 'location_description',
        'latitude', 'longitude', 'alert_level', 'dispatched_at', 'resolved_at',
        'post_incident_report',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'resolved_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }
}
