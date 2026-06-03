<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $fillable = [
        'recipient',
        'message',
        'type',
        'status',
        'error_message',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
