<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_vacancy_id', 'full_name', 'email', 'resume_url', 'pipeline_stage', 'assessment_scores'
    ];

    protected $casts = [
        'assessment_scores' => 'array',
    ];

    public function vacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }
}