<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementBadge extends Model
{
    use HasFactory;

    protected $table = 'achievement_badges';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon_emoji',
        'category',
        'criteria',
        'xp_reward',
    ];

    protected $casts = [
        'criteria' => 'array',
        'xp_reward' => 'integer',
    ];
}
