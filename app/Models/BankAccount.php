<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BankAccount extends Model {
    use HasFactory;
    protected $fillable = ['bank_name', 'account_number', 'account_name', 'account_type', 'current_balance', 'is_primary', 'is_active'];
    protected $casts = ['is_primary' => 'boolean', 'is_active' => 'boolean', 'current_balance' => 'decimal:2'];
    public function statements() { return $this->hasMany(BankStatement::class); }
    public function dailyPositions() { return $this->hasMany(DailyCashPosition::class); }
    public function latestPosition() { return $this->hasOne(DailyCashPosition::class)->ofMany('position_date', 'max'); }
}