<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwotItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'swot_analysis_id',
        'quadrant',
        'description',
        'impact_score',
        'likelihood_score',
        'strategy_response',
        'sort_order',
    ];
}
