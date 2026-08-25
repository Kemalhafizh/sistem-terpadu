<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'forecast_month',
        'projected_tuition_inflow',
        'projected_operational_outflow',
        'projected_net_cash',
        'confidence_lower',
        'confidence_upper',
        'historical_trend_factor',
        'forecast_scenario',
    ];

    protected $casts = [
        'projected_tuition_inflow' => 'float',
        'projected_operational_outflow' => 'float',
        'projected_net_cash' => 'float',
        'confidence_lower' => 'float',
        'confidence_upper' => 'float',
        'historical_trend_factor' => 'float',
    ];
}
