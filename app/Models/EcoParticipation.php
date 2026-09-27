<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcoParticipation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'challenge_type',
        'participation_date',
        'points_awarded',
        'evidence_url',
        'is_verified',
        'verified_by',
    ];

    protected $casts = [
        'participation_date' => 'date',
        'is_verified' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function verifier()
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }
}
