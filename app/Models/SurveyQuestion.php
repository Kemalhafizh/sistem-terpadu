<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question_text',
        'type',
        'options',
        'is_required',
        'order',
        'scale_min',
        'scale_max',
        'scale_min_label',
        'scale_max_label',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'order' => 'integer',
        'scale_min' => 'integer',
        'scale_max' => 'integer',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
