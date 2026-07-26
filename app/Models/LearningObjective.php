<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_outcome_id',
        'tp_code',
        'tp_description',
        'semester',
        'sequence_order',
        'assessment_type',
        'bloom_taxonomy_level',
    ];

    public function outcome(): BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class, 'learning_outcome_id');
    }

    public function atpMappings(): HasMany
    {
        return $this->hasMany(AtpMapping::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(CompetencyAchievement::class);
    }
}
