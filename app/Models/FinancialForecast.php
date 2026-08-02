<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialForecast extends Model
{
    use HasFactory;

    protected $table = 'financial_forecasts';

    protected $fillable = [
        'academic_year_id',
        'scenario_name',
        'projected_revenue',
        'projected_expense',
        'confidence_interval',
        'parameters',
        'created_by',
    ];

    protected $casts = [
        'parameters' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
