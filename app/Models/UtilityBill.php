<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityBill extends Model
{
    use HasFactory;

    protected $table = 'utility_bills';

    protected $fillable = [
        'utility_type',
        'billing_period',
        'amount',
        'usage_kwh_m3',
        'is_paid',
        'due_date',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'due_date' => 'date',
    ];
}
