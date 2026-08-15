<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizLeaderboard extends Model
{
    use HasFactory;
    protected $fillable = ['gamified_quiz_id', 'student_id', 'total_score', 'correct_answers', 'streak_count'];
    public function quiz() { return $this->belongsTo(GamifiedQuiz::class, 'gamified_quiz_id'); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
