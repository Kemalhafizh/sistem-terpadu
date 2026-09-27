<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitInterview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'interviewer_id', 'interview_date', 'reasons_for_leaving', 'constructive_feedback', 'status',
    ];

    protected $casts = [
        'reasons_for_leaving' => 'array',
        'interview_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
