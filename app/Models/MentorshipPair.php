<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorshipPair extends Model
{
    use HasFactory;

    protected $table = 'mentorship_pairs';

    protected $fillable = [
        'mentor_profile_id',
        'student_id',
        'goals',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

    public function mentorProfile(): BelongsTo
    {
        return $this->belongsTo(MentorProfile::class, 'mentor_profile_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
