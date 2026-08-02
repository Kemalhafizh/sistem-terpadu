<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VolunteerOpportunity extends Model
{
    use HasFactory;

    protected $table = 'volunteer_opportunities';

    protected $fillable = [
        'title',
        'description',
        'required_volunteers',
        'required_hours',
        'event_date',
        'location',
        'status',
        'organizer_id',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VolunteerLog::class, 'opportunity_id');
    }
}
