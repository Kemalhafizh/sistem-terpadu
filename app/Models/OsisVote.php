<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsisVote extends Model
{
    protected $fillable = [
        'election_id',
        'voter_id',
        'candidate_id',
    ];

    public function election()
    {
        return $this->belongsTo(OsisElection::class, 'election_id');
    }

    public function voter()
    {
        return $this->belongsTo(Student::class, 'voter_id');
    }

    public function candidate()
    {
        return $this->belongsTo(OsisCandidate::class, 'candidate_id');
    }
}
