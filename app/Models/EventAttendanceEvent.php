<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventAttendanceEvent extends Model
{
    use HasFactory;

    protected $table = 'event_attendance_events';

    protected $fillable = [
        'name',
        'type',
        'event_date',
        'start_time',
        'end_time',
        'target',
        'target_ids',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'target_ids' => 'array',
    ];

    public function records()
    {
        return $this->hasMany(EventAttendanceRecord::class, 'event_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
