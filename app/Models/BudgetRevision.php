<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_plan_id',
        'revision_number',
        'reason',
        'revised_by',
        'old_total',
        'new_total',
    ];

    protected $casts = [
        'old_total' => 'decimal:2',
        'new_total' => 'decimal:2',
    ];

    public function budgetPlan(): BelongsTo
    {
        return $this->belongsTo(BudgetPlan::class);
    }

    public function reviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}
