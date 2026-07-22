<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityThreatLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'severity',
        'ip_address',
        'user_agent',
        'payload_data',
        'is_resolved',
    ];

    protected $casts = [
        'payload_data' => 'array',
        'is_resolved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Color helper badge based on severity.
     */
    public function getSeverityBadgeAttribute()
    {
        return match ($this->severity) {
            'critical' => 'bg-rose-500 text-white',
            'high' => 'bg-orange-500 text-white',
            'medium' => 'bg-amber-500 text-white',
            'low' => 'bg-slate-500 text-white',
            default => 'bg-blue-500 text-white',
        };
    }
}
