<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatisfactionDimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'dimension_name',
        'weight',
        'sort_order',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(SatisfactionQuestion::class, 'dimension_id');
    }
}
