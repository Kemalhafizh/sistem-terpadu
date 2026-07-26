<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtpMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_objective_id',
        'week_number',
        'teaching_hours',
        'learning_activities',
        'assessment_method',
        'resources',
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class, 'learning_objective_id');
    }
}
