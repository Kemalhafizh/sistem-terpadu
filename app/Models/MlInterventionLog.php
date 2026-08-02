<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MlInterventionLog extends Model
{
    use HasFactory;

    protected $table = 'ml_intervention_logs';

    protected $fillable = [
        'prediction_id',
        'counselor_id',
        'action_taken',
        'status',
    ];

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(MlPerformancePrediction::class, 'prediction_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
