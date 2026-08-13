<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategicObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'strategic_plan_id',
        'perspective',
        'code',
        'objective',
        'description',
        'weight',
        'sort_order',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class, 'strategic_plan_id');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(KeyPerformanceIndicator::class, 'strategic_objective_id');
    }
}
