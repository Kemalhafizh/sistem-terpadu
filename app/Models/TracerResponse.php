<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class TracerResponse extends Model
{
    use HasUuid;

    protected $fillable = [
        'alumni_data_id',
        'job_title',
        'salary_range',
        'study_major',
        'relevance_score',
        'satisfaction_score',
        'suggestions',
        'employer_name',
        'entry_year',
    ];

    public function alumni()
    {
        return $this->belongsTo(AlumniData::class, 'alumni_data_id');
    }
}
