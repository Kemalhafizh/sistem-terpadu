<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketedEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'description',
        'event_datetime',
        'venue',
        'max_capacity',
        'is_public',
        'organizer_id',
    ];

    protected $casts = [
        'event_datetime' => 'datetime',
        'is_public' => 'boolean',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function tickets()
    {
        return $this->hasMany(EventTicket::class);
    }
}
