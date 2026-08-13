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

    public function dimensions(): HasMany
    {
        return $this->hasMany(SatisfactionDimension::class, 'survey_id');
    }
}
