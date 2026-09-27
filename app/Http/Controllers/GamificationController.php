<?php

namespace App\Http\Controllers;

use App\Models\AchievementDefinition;
use App\Models\Classroom;
use App\Models\StudentAchievement;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * View school-wide or classroom leaderboard.
     */
    public function leaderboard(Request $request)
    {
        $classrooms = Classroom::all();
        $classroomId = $request->filled('classroom_id') ? (int) $request->classroom_id : null;

        $leaderboard = $this->gamificationService->getLeaderboard('all_time', $classroomId);

        return view('gamification.leaderboard', compact('leaderboard', 'classrooms', 'classroomId'));
    }

    /**
     * View logged-in student's gamification progress dashboard.
     */
    public function myProgress()
    {
        $user = auth()->user();
        if ($user->role !== 'student' || ! $user->student) {
            return redirect()->route('dashboard')->with('error', 'Halaman ini hanya dapat diakses oleh siswa aktif.');
        }

        $student = $user->student;
        $student->load(['xpLogs' => function ($q) {
            $q->latest()->take(10);
        }, 'achievements.definition']);

        return view('gamification.my-progress', compact('student'));
    }

    /**
     * View list of achievements catalog (earned vs locked).
     */
    public function achievements()
    {
        $user = auth()->user();
        if ($user->role !== 'student' || ! $user->student) {
            return redirect()->route('dashboard')->with('error', 'Katalog pencapaian hanya dapat diakses oleh siswa.');
        }

        $student = $user->student;
        $definitions = AchievementDefinition::active()->get();
        $earnedIds = StudentAchievement::where('student_id', $student->id)
            ->pluck('achievement_id')
            ->toArray();

        return view('gamification.achievements', compact('definitions', 'earnedIds', 'student'));
    }
}
