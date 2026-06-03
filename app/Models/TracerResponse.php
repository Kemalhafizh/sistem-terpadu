<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerResponse extends Model
{
    protected $fillable = [
        'alumni_data_id',
        'job_title',
        'salary_range',
        'study_major',
        'relevance_score',
        'satisfaction_score',
        'suggestions',
    ];

    public function alumni()
    {
        return $this->belongsTo(AlumniData::class, 'alumni_data_id');
    }
}
