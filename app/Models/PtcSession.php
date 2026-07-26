<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PtcSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'academic_year',
        'semester',
        'date',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'location',
        'organizer_id',
        'status',
        'max_slots_per_teacher',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(PtcSlot::class);
    }
}
