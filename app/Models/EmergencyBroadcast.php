<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyBroadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'crisis_incident_id', 'title', 'message', 'priority', 'target_groups',
        'channels', 'total_recipients', 'delivered_count', 'read_count', 'sent_by', 'sent_at'
    ];

    protected $casts = [
        'target_groups' => 'array',
        'channels' => 'array',
        'sent_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(CrisisIncident::class, 'crisis_incident_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function receipts()
    {
        return $this->hasMany(EmergencyBroadcastReceipt::class, 'broadcast_id');
    }
}