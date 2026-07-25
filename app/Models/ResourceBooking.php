<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'bookable_resource_id',
        'booked_by',
        'purpose',
        'start_datetime',
        'end_datetime',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(BookableResource::class, 'bookable_resource_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
