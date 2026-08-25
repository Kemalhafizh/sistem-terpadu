<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecutiveKpi extends Model
{
    use HasFactory;

    protected $table = 'executive_kpis';

    protected $fillable = [
        'metric_name',
        'category',
        'current_value',
        'target_value',
        'previous_value',
        'trend_direction',
        'unit',
        'calculated_at',
    ];

    protected $casts = [
        'current_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'previous_value' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];
}
