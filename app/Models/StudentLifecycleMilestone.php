<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLifecycleMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'milestone_type',
        'achieved_at',
        'description',
    ];

    protected $casts = [
        'achieved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
