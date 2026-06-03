<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'color',
        'category',
        'rarity',
        'xp_reward',
        'criteria',
        'is_auto',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_auto' => 'boolean',
        'is_active' => 'boolean',
        'xp_reward' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
