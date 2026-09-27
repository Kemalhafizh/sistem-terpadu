<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'user_id',
        'answers',
        'ip_address',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the respondent user model, respecting the survey's anonymity setting.
     * Guaranteed safety check for PDP compliance.
     */
    public function getRespondentAttribute()
    {
        if ($this->survey && $this->survey->is_anonymous) {
            return null;
        }

        return $this->user;
    }
}
