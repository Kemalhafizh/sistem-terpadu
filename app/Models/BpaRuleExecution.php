<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpaRuleExecution extends Model
{
    use HasFactory;

    protected $table = 'bpa_rule_executions';

    protected $fillable = [
        'bpa_business_rule_id',
        'trigger_event',
        'matched',
        'actions_executed',
        'status',
        'execution_time_ms',
    ];

    protected $casts = [
        'matched' => 'boolean',
        'actions_executed' => 'array',
        'execution_time_ms' => 'float',
    ];

    public function rule()
    {
        return $this->belongsTo(BpaBusinessRule::class, 'bpa_business_rule_id');
    }
}
