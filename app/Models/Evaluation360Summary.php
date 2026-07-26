<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation360Summary extends Model
{
    use HasFactory;

    protected $table = 'evaluation_360_summaries';

    protected $fillable = [
        'cycle_id',
        'evaluatee_id',
        'supervisor_avg',
        'peer_avg',
        'student_avg',
        'parent_avg',
        'self_score',
        'overall_score',
        'strengths',
        'improvements',
        'generated_at',
    ];

    protected $casts = [
        'strengths' => 'array',
        'improvements' => 'array',
        'generated_at' => 'datetime',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Evaluation360Cycle::class, 'cycle_id');
    }

    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }
}
