<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkplanProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'annual_workplan_id',
        'strategic_objective_id',
        'program_code',
        'program_name',
        'description',
        'category',
        'budget_allocation',
        'realization',
        'quarter',
        'pic_user_id',
        'status',
        'progress_percentage',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(WorkplanActivity::class, 'program_id');
    }
}
