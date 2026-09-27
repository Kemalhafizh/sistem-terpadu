<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCashPosition extends Model
{
    use HasFactory;

    protected $fillable = ['position_date', 'bank_account_id', 'opening_balance', 'total_inflow', 'total_outflow', 'closing_balance'];

    protected $casts = ['position_date' => 'date', 'opening_balance' => 'decimal:2', 'total_inflow' => 'decimal:2', 'total_outflow' => 'decimal:2', 'closing_balance' => 'decimal:2'];

    public function account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
