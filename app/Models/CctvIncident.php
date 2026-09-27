<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CctvIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'camera_id', 'zone_name', 'incident_time', 'incident_type',
        'snapshot_url', 'status', 'resolution_notes', 'resolved_by',
    ];

    protected $casts = [
        'incident_time' => 'datetime',
    ];

    public function resolver()
    {
        return $this->belongsTo(Employee::class, 'resolved_by');
    }
}
