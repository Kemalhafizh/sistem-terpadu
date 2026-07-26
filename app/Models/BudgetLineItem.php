<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_plan_id',
        'category',
        'sub_category',
        'account_code',
        'description',
        'planned_amount',
        'actual_amount',
        'variance_amount',
        'quarter',
        'notes',
    ];

    protected $casts = [
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
    ];

    public function budgetPlan(): BelongsTo
    {
        return $this->belongsTo(BudgetPlan::class);
    }
}
