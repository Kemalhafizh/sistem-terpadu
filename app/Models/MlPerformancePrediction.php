<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MlPerformancePrediction extends Model
{
    use HasFactory;

    protected $table = 'ml_performance_predictions';

    protected $fillable = [
        'student_id',
        'term_id',
        'predicted_gpa',
        'risk_level',
        'factors',
        'prediction_date',
    ];

    protected $casts = [
        'factors' => 'array',
        'prediction_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(MlInterventionLog::class, 'prediction_id');
    }
}
