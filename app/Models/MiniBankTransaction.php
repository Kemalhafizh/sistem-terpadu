<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniBankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id', 'transaction_type', 'amount', 'reference_code', 'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(StudentWallet::class, 'wallet_id');
    }
}
