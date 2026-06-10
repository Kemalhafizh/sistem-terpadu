<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAttendanceRecord extends Model
{
    protected $table = 'event_attendance_records';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'check_in_time',
        'notes',
    ];

    public function event()
    {
        return $this->belongsTo(EventAttendanceEvent::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
