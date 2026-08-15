<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegacyPledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_name',
        'donor_type',
        'pledged_amount',
        'fund_designation',
        'expected_fulfillment_date',
        'status',
        'naming_recognition_notes',
    ];

    protected $casts = [
        'pledged_amount' => 'decimal:2',
        'expected_fulfillment_date' => 'date',
    ];
}
