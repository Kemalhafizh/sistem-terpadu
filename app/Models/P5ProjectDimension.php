<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5ProjectDimension extends Model
{
    protected $fillable = [
        'p5_project_id',
        'character_dimension_id',
        'element',
        'sub_element',
        'target_achievement',
        'display_order',
    ];

    /** @return BelongsTo<P5Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    /** @return BelongsTo<CharacterDimension, $this> */
    public function characterDimension(): BelongsTo
    {
        return $this->belongsTo(CharacterDimension::class, 'character_dimension_id');
    }

    /** @return HasMany<P5ProjectAssessment, $this> */
    public function assessments(): HasMany
    {
        return $this->hasMany(P5ProjectAssessment::class, 'p5_project_dimension_id');
    }
}
