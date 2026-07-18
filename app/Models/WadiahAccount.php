<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WadiahAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'account_number',
        'balance',
        'daily_spending_limit',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'daily_spending_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function transactions()
    {
        return $this->hasMany(WadiahTransaction::class);
    }

    /**
     * Get remaining daily spending limit for today.
     */
    public function getRemainingDailyLimitAttribute()
    {
        $todaySpent = $this->transactions()
            ->where('type', 'purchase')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $remaining = $this->daily_spending_limit - $todaySpent;

        return (float) max(0.00, $remaining);
    }
}
