<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SwotAnalysis extends Model
{
    use HasFactory;

    protected $table = 'swot_analyses';

    protected $fillable = [
        'strategic_plan_id',
        'title',
        'analysis_year',
        'created_by',
        'conclusion',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SwotItem::class, 'swot_analysis_id');
    }
}
