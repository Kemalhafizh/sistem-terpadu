<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiscal_year',
        'title',
        'status',
        'total_revenue_planned',
        'total_expense_planned',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'total_revenue_planned' => 'decimal:2',
        'total_expense_planned' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function lineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(BudgetRevision::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
