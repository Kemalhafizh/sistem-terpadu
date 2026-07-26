<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtcSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'ptc_session_id',
        'teacher_id',
        'start_time',
        'end_time',
        'parent_id',
        'student_id',
        'topic',
        'status',
        'notes',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PtcSession::class, 'ptc_session_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
