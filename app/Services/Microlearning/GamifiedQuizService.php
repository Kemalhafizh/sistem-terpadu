<?php

namespace App\Services\Microlearning;

use App\Models\GamifiedQuiz;
use Illuminate\Support\Str;

class GamifiedQuizService
{
    public function createLobby($teacherId, $title)
    {
        return GamifiedQuiz::create(['teacher_id' => $teacherId, 'title' => $title, 'join_code' => Str::random(6), 'status' => 'waiting']);
    }

    public function joinStudent($joinCode, $studentId) {}

    public function startQuiz(GamifiedQuiz $quiz)
    {
        $quiz->update(['status' => 'live']);
    }

    public function processAnswerScore() {}

    public function updateLeaderboard() {}

    public function endQuiz(GamifiedQuiz $quiz)
    {
        $quiz->update(['status' => 'finished']);
    }
}
