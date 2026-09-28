<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkflowRequest extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'definition_id',
        'requester_id',
        'requestable_type',
        'requestable_id',
        'current_step',
        'status',
        'notes',
        'due_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    /** @return BelongsTo<WorkflowDefinition, $this> */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requestable()
    {
        return $this->morphTo();
    }

    /** @return HasMany<WorkflowApproval, $this> */
    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class, 'request_id')->orderBy('step_order');
    }

    /**
     * Open requests whose current step is this user's to decide: their role
     * (not delegated away) or delegated to them. whereHas('currentApproval')
     * cannot express this — that relation reads current_step from the model
     * instance, which is empty inside a query, so it matched nothing.
     *
     * @param  Builder<WorkflowRequest>  $query
     * @return Builder<WorkflowRequest>
     */
    public function scopeAwaitingDecisionBy(Builder $query, User $user): Builder
    {
        return $query->whereIn('status', ['pending', 'in_review'])
            ->whereHas('approvals', function ($q) use ($user) {
                $q->whereColumn('workflow_approvals.step_order', 'workflow_requests.current_step')
                    ->where(function ($q) use ($user) {
                        $q->where(fn ($q) => $q->where('role', $user->role)->whereNull('delegated_to_id'))
                            ->orWhere('delegated_to_id', $user->id);
                    });
            });
    }

    /** @return HasOne<WorkflowApproval, $this> */
    public function currentApproval(): HasOne
    {
        return $this->hasOne(WorkflowApproval::class, 'request_id')
            ->where('step_order', $this->current_step);
    }
}
