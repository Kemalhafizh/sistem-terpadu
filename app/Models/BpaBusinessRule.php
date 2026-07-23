<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpaBusinessRule extends Model
{
    use HasFactory;

    protected $table = 'bpa_business_rules';

    protected $fillable = [
        'name',
        'trigger_event',
        'conditions',
        'actions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
    ];

    public function executions()
    {
        return $this->hasMany(BpaRuleExecution::class, 'bpa_business_rule_id');
    }
}
