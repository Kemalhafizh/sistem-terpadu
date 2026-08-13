<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeyPerformanceIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'strategic_objective_id',
        'code',
        'indicator',
        'unit',
        'baseline_value',
        'target_value',
        'current_value',
        'polarity',
        'frequency',
        'pic_user_id',
        'data_source',
        'formula',
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(StrategicObjective::class, 'strategic_objective_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(KpiMeasurement::class, 'kpi_id');
    }
}
