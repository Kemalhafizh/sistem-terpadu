<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatisfactionQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'dimension_id',
        'question_text',
        'scale_type',
        'sort_order',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(SatisfactionResponse::class, 'question_id');
    }
}
