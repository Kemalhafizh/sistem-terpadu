<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'step_order',
        'role',
        'approver_id',
        'delegated_to_id',
        'decision',
        'remarks',
        'due_at',
        'decided_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    /** @return BelongsTo<WorkflowRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(WorkflowRequest::class, 'request_id');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /** @return BelongsTo<User, $this> */
    public function delegatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to_id');
    }
}
