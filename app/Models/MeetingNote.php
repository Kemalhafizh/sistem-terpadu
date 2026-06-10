<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'created_by',
        'agenda',
        'discussion',
        'decisions',
        'action_items',
        'attendees',
        'status',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'action_items' => 'array',
        'attendees' => 'array',
    ];

    /**
     * Get the user who created the meeting note.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
