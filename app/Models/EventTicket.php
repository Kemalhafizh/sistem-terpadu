<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticketed_event_id',
        'ticket_code',
        'attendee_name',
        'attendee_email',
        'ticket_type',
        'price',
        'is_scanned',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_scanned' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(TicketedEvent::class, 'ticketed_event_id');
    }
}
