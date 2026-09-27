<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatement extends Model
{
    use HasFactory;

    protected $fillable = ['bank_account_id', 'statement_date', 'period_start', 'period_end', 'file_path', 'total_transactions', 'matched_count', 'unmatched_count', 'status', 'imported_by'];

    protected $casts = ['statement_date' => 'date', 'period_start' => 'date', 'period_end' => 'date'];

    public function account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }
}
