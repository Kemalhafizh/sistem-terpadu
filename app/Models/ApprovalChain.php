<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalChain extends Model
{
    use HasFactory;

    protected $table = 'approval_chains';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'workflow_blueprint_id',
        'current_node_key',
        'status',
        'requester_id',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(WorkflowBlueprint::class, 'workflow_blueprint_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProcessMiningLog::class);
    }
}
