<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrtStudentAbility extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'theta',
        'standard_error',
        'items_attempted',
        'last_estimated_at',
    ];

    protected $casts = [
        'theta' => 'float',
        'standard_error' => 'float',
        'items_attempted' => 'integer',
        'last_estimated_at' => 'datetime',
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
