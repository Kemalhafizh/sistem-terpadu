<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsisElection extends Model
{
    protected $fillable = [
        'academic_year_id',
        'title',
        'voting_start',
        'voting_end',
        'status',
    ];

    protected $casts = [
        'voting_start' => 'date',
        'voting_end' => 'date',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function candidates()
    {
        return $this->hasMany(OsisCandidate::class, 'election_id');
    }

    public function votes()
    {
        return $this->hasMany(OsisVote::class, 'election_id');
    }
}
