<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualClassroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'provider',
        'meeting_id',
        'meeting_url',
        'meeting_password',
        'host_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'recording_url',
        'attendees_count',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(VirtualClassroomAttendance::class);
    }
}
