<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'journal_date', 'activities', 'reflection', 'verified_by'
    ];

    protected $casts = [
        'journal_date' => 'date',
    ];

    public function application()
    {
        return $this->belongsTo(InternshipApplication::class, 'application_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
