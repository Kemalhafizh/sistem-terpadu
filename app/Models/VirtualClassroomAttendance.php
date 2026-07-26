<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualClassroomAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'virtual_classroom_id',
        'user_id',
        'joined_at',
        'left_at',
        'duration_minutes',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(VirtualClassroom::class, 'virtual_classroom_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
