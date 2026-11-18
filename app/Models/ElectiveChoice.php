<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectiveChoice extends Model
{
    protected $fillable = [
        'elective_survey_id',
        'student_id',
        'subject_id',
        'rank',
    ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
        ];
    }

    /** @return BelongsTo<ElectiveSurvey, $this> */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(ElectiveSurvey::class, 'elective_survey_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Subject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
