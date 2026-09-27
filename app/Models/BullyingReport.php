<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BullyingReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number', 'description', 'type', 'severity', 'location', 'incident_date',
        'is_anonymous', 'reporter_id', 'reporter_alias', 'status', 'assigned_to',
        'investigation_notes', 'action_taken', 'follow_up_plan', 'is_escalated',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'is_anonymous' => 'boolean',
        'is_escalated' => 'boolean',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function logs()
    {
        return $this->hasMany(BullyingInvestigationLog::class);
    }
}
