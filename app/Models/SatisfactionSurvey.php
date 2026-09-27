<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatisfactionSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'survey_type',
        'description',
        'start_date',
        'end_date',
        'is_anonymous',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_anonymous' => 'boolean',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(SatisfactionResponse::class, 'survey_id');
    }

    /**
     * Open for answers: active and today within the survey period.
     */
    public function isOpen(): bool
    {
        return $this->status === 'active' && now()->between($this->start_date->startOfDay(), $this->end_date->endOfDay());
    }

    public function dimensions(): HasMany
    {
        return $this->hasMany(SatisfactionDimension::class, 'survey_id');
    }
}
