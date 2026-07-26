<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceivedNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'grn_number',
        'received_by',
        'received_date',
        'items_received',
        'condition_notes',
        'is_complete',
    ];

    protected $casts = [
        'items_received' => 'array',
        'received_date' => 'date',
        'is_complete' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
