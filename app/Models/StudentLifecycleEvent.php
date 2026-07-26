<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLifecycleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'event_type',
        'from_status',
        'to_status',
        'academic_year',
        'semester',
        'triggered_by',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function triggerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
