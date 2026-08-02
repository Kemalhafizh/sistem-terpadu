<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForensicEvent extends Model
{
    use HasFactory;

    protected $table = 'forensic_events';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'payload',
        'severity',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
