<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafetyHeadcount extends Model
{
    use HasFactory;

    protected $fillable = [
        'crisis_incident_id', 'classroom_id', 'assembly_point', 'expected_count',
        'accounted_count', 'missing_count', 'missing_persons', 'status', 'verified_by', 'completed_at',
    ];

    protected $casts = [
        'missing_persons' => 'array',
        'completed_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(CrisisIncident::class, 'crisis_incident_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
