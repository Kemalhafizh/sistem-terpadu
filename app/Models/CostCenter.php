<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'department',
        'head_id',
        'annual_budget_allocation',
        'spent_amount',
    ];

    protected $casts = [
        'annual_budget_allocation' => 'decimal:2',
        'spent_amount' => 'decimal:2',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CostCenterTransaction::class);
    }
}
