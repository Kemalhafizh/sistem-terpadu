<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashFund extends Model
{
    use HasFactory;

    protected $fillable = ['fund_name', 'custodian_id', 'imprest_amount', 'current_balance', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'imprest_amount' => 'decimal:2', 'current_balance' => 'decimal:2'];

    public function custodian()
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }

    public function transactions()
    {
        return $this->hasMany(PettyCashTransaction::class, 'fund_id');
    }
}
