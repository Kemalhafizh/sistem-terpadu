<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'phase',
        'element',
        'cp_code',
        'cp_description',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(LearningObjective::class);
    }
}
