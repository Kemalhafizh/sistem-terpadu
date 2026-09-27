<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyBroadcastReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'broadcast_id', 'user_id', 'status', 'channel', 'delivered_at', 'read_at', 'acknowledged_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function broadcast()
    {
        return $this->belongsTo(EmergencyBroadcast::class, 'broadcast_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
