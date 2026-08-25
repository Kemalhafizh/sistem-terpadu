<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRiskProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_risk_score',
        'attendance_risk_score',
        'behavioral_risk_score',
        'composite_risk_level',
        'predicted_dropout_probability',
        'risk_factors',
        'intervention_recommendations',
        'assessed_at',
    ];

    protected $casts = [
        'academic_risk_score' => 'float',
        'attendance_risk_score' => 'float',
        'behavioral_risk_score' => 'float',
        'predicted_dropout_probability' => 'float',
        'risk_factors' => 'array',
        'intervention_recommendations' => 'array',
        'assessed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
