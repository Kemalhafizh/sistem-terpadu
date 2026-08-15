<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IepDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'case_manager_id',
        'start_date',
        'review_date',
        'diagnoses',
        'present_level_of_performance',
        'annual_goals',
        'required_accommodations',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'review_date' => 'date',
        'diagnoses' => 'array',
        'annual_goals' => 'array',
        'required_accommodations' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function caseManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'case_manager_id');
    }
}
