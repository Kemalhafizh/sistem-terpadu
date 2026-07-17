<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowRequest extends Model
{
    use \App\Traits\HasUuid, HasFactory;

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

    public function definition()
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requestable()
    {
        return $this->morphTo();
    }

    public function approvals()
    {
        return $this->hasMany(WorkflowApproval::class, 'request_id')->orderBy('step_order');
    }

    public function currentApproval()
    {
        return $this->hasOne(WorkflowApproval::class, 'request_id')
            ->where('step_order', $this->current_step);
    }
}
