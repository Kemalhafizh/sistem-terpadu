<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowNode extends Model
{
    use HasFactory;

    protected $table = 'workflow_nodes';

    protected $fillable = [
        'workflow_blueprint_id',
        'node_key',
        'node_name',
        'node_type',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'array',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(WorkflowBlueprint::class, 'workflow_blueprint_id');
    }
}
