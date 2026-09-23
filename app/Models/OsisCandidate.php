<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsisCandidate extends Model
{
    protected $fillable = [
        'election_id',
        'student_id',
        'vice_student_id',
        'candidate_number',
        'vision',
        'mission',
        'photo_path',
        'vote_count',
    ];

    public function election()
    {
        return $this->belongsTo(OsisElection::class, 'election_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function viceStudent()
    {
        return $this->belongsTo(Student::class, 'vice_student_id');
    }
}
