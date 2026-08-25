<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowBlueprint extends Model
{
    use HasFactory;

    protected $table = 'workflow_blueprints';

    protected $fillable = [
        'name',
        'process_type',
        'bpmn_schema',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'bpmn_schema' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(WorkflowNode::class);
    }

    public function slaDefinitions(): HasMany
    {
        return $this->hasMany(SlaDefinition::class);
    }
}
