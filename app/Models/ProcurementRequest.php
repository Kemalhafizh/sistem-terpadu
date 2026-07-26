<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProcurementRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'department',
        'requested_by',
        'urgency',
        'estimated_budget',
        'status',
        'approved_by',
        'approval_notes',
    ];

    protected $casts = [
        'estimated_budget' => 'decimal:2',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }
}
