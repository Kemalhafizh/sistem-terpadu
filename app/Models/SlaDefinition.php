<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaDefinition extends Model
{
    use HasFactory;

    protected $table = 'sla_definitions';

    protected $fillable = [
        'workflow_blueprint_id',
        'node_key',
        'max_duration_hours',
        'escalation_action',
    ];

    protected $casts = [
        'max_duration_hours' => 'integer',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(WorkflowBlueprint::class, 'workflow_blueprint_id');
    }
}
