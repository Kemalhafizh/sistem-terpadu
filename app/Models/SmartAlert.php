<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'severity',
        'title',
        'description',
        'context_data',
        'alertable_type',
        'alertable_id',
        'status',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'escalated_to_id',
        'escalation_notes',
        'escalated_at',
    ];

    protected $casts = [
        'context_data' => 'array',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    public function alertable()
    {
        return $this->morphTo();
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }
}
