<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'balance', 'savings_target', 'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'savings_target' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function transactions()
    {
        return $this->hasMany(MiniBankTransaction::class, 'wallet_id');
    }
}
