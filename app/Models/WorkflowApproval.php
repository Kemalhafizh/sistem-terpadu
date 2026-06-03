<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'step_order',
        'role',
        'approver_id',
        'decision',
        'remarks',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(WorkflowRequest::class, 'request_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
