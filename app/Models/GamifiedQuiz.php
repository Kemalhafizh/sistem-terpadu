<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamifiedQuiz extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'title', 'join_code', 'status', 'game_settings'];

    protected $casts = [
        'game_settings' => 'array',
    ];

    public function leaderboards()
    {
        return $this->hasMany(QuizLeaderboard::class);
    }
}
