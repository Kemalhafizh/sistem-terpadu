<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyMasteryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'competency_code',
        'competency_description',
        'mastery_level',
        'mastery_score',
        'attempts',
        'last_assessed_at',
    ];

    protected $casts = [
        'mastery_score' => 'float',
        'attempts' => 'integer',
        'last_assessed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
