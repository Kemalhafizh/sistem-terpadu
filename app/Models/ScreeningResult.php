<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'evaluator_id',
        'screening_type',
        'responses',
        'total_score',
        'risk_level',
        'system_recommendation',
        'requires_professional_referral',
    ];

    protected $casts = [
        'responses' => 'array',
        'requires_professional_referral' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }
}
