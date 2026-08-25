<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessMiningLog extends Model
{
    use HasFactory;

    protected $table = 'process_mining_logs';

    protected $fillable = [
        'approval_chain_id',
        'node_key',
        'duration_minutes',
        'was_escalated',
        'recorded_at',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'was_escalated' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function chain(): BelongsTo
    {
        return $this->belongsTo(ApprovalChain::class, 'approval_chain_id');
    }
}
