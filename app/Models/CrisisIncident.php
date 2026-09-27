<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrisisIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'title', 'type', 'severity', 'status', 'description', 'location_detail',
        'detected_at', 'responded_at', 'contained_at', 'resolved_at', 'reported_by',
        'incident_commander_id', 'affected_students_count', 'affected_staff_count',
        'injured_count', 'evacuation_zones', 'post_mortem_notes',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'responded_at' => 'datetime',
        'contained_at' => 'datetime',
        'resolved_at' => 'datetime',
        'evacuation_zones' => 'array',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function commander()
    {
        return $this->belongsTo(User::class, 'incident_commander_id');
    }

    public function logs()
    {
        return $this->hasMany(CrisisResponseLog::class);
    }
}
