<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'company_id', 'supervisor_id', 'start_date', 'end_date', 'status', 'final_score',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'final_score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function company()
    {
        return $this->belongsTo(InternshipCompany::class, 'company_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function journals()
    {
        return $this->hasMany(InternshipJournal::class, 'application_id');
    }
}
