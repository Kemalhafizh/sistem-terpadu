<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahfidzLog extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'surah_number',
        'surah_name',
        'from_ayat',
        'to_ayat',
        'type',
        'grade',
        'notes',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
