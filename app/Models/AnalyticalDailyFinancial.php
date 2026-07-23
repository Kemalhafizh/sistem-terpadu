<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticalDailyFinancial extends Model
{
    use HasFactory;

    protected $table = 'analytical_daily_financials';

    protected $fillable = [
        'date',
        'total_spp_collected',
        'total_wadiah_collected',
        'total_canteen_transactions',
        'cash_balance',
        'transaction_count',
    ];

    protected $casts = [
        'date' => 'date',
        'total_spp_collected' => 'decimal:2',
        'total_wadiah_collected' => 'decimal:2',
        'total_canteen_transactions' => 'decimal:2',
        'cash_balance' => 'decimal:2',
        'transaction_count' => 'integer',
    ];
}
