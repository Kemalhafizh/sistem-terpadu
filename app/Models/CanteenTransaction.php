<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanteenTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'canteen_merchant_id',
        'wadiah_transaction_id',
        'items_ordered',
        'total_amount',
    ];

    protected $casts = [
        'items_ordered' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function merchant()
    {
        return $this->belongsTo(CanteenMerchant::class, 'canteen_merchant_id');
    }

    public function wadiahTransaction()
    {
        return $this->belongsTo(WadiahTransaction::class, 'wadiah_transaction_id');
    }
}
