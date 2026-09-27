<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnualWorkplan extends Model
{
    use HasFactory;

    protected $fillable = [
        'strategic_plan_id',
        'title',
        'fiscal_year',
        'status',
        'total_budget',
        'total_realization',
        'prepared_by',
        'approved_by',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class, 'strategic_plan_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(WorkplanProgram::class);
    }
}
