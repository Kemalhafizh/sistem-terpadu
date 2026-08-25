<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialHealthMetric extends Model
{
    use HasFactory;

    protected $table = 'financial_health_metrics';

    protected $fillable = [
        'report_date',
        'cash_reserve',
        'monthly_burn_rate',
        'projected_revenue',
        'fee_collection_rate',
        'expense_distribution',
    ];

    protected $casts = [
        'report_date' => 'date',
        'cash_reserve' => 'decimal:2',
        'monthly_burn_rate' => 'decimal:2',
        'projected_revenue' => 'decimal:2',
        'fee_collection_rate' => 'decimal:2',
        'expense_distribution' => 'array',
    ];
}
