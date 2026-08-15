<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WellnessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'wellness_program_id', 'participation_date', 'feedback_note'
    ];

    protected $casts = [
        'participation_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function program()
    {
        return $this->belongsTo(WellnessProgram::class, 'wellness_program_id');
    }
}