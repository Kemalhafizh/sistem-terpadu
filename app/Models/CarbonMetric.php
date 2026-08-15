<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarbonMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'measurement_date',
        'electricity_kwh',
        'water_usage_m3',
        'transport_emissions_kg',
        'paper_usage_kg',
        'total_carbon_footprint_kg',
        'source_data',
        'reduction_initiatives'
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'source_data' => 'array',
        'electricity_kwh' => 'decimal:2',
        'water_usage_m3' => 'decimal:2',
        'transport_emissions_kg' => 'decimal:2',
        'paper_usage_kg' => 'decimal:2',
        'total_carbon_footprint_kg' => 'decimal:2',
    ];
}
