<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WadiahTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wadiah_account_id',
        'type',
        'amount',
        'previous_balance',
        'current_balance',
        'reference_number',
        'description',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(WadiahAccount::class, 'wadiah_account_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function canteenTransaction()
    {
        return $this->hasOne(CanteenTransaction::class, 'wadiah_transaction_id');
    }
}
