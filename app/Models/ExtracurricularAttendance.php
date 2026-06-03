<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtracurricularAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'date',
        'student_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
