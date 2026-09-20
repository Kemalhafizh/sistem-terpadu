<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    use ApiResponseTrait;

    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * GET /api/v1/gamification/profile
     */
    public function getProfile(Request $request)
    {
        $userId = $request->user()->id;
        $profile = $this->gamificationService->getProfile($userId);
        return $this->successResponse($profile, 'Gamification profile retrieved successfully');
    }

    /**
     * GET /api/v1/gamification/leaderboard
     */
    public function getLeaderboard(Request $request)
    {
        $scope = $request->query('scope', 'school');
        $period = $request->query('period', 'monthly');

        $leaderboard = [
            ['rank' => 1, 'name' => 'Ahmad Dahlan Z.', 'className' => 'XII MIPA 1', 'xp' => 1850, 'isCurrentUser' => false],
            ['rank' => 2, 'name' => 'Fatimah Az-Zahra', 'className' => 'XII MIPA 1', 'xp' => 1640, 'isCurrentUser' => false],
            ['rank' => 3, 'name' => 'Muhammad Kemal H.', 'className' => 'XII MIPA 1', 'xp' => 1420, 'isCurrentUser' => true],
            ['rank' => 4, 'name' => 'Salma Nur Azizah', 'className' => 'XII MIPA 2', 'xp' => 1290, 'isCurrentUser' => false],
            ['rank' => 5, 'name' => 'Rizky Ramadhan', 'className' => 'XII IPS 1', 'xp' => 1150, 'isCurrentUser' => false],
            ['rank' => 6, 'name' => 'Zahra Aulia Putri', 'className' => 'XII MIPA 2', 'xp' => 1080, 'isCurrentUser' => false],
            ['rank' => 7, 'name' => 'Dimas Arya Pratama', 'className' => 'XII IPS 2', 'xp' => 990, 'isCurrentUser' => false],
        ];

        return $this->successResponse($leaderboard, 'Leaderboard data retrieved successfully');
    }

    /**
     * GET /api/v1/gamification/badges
     */
    public function getBadges(Request $request)
    {
        $userId = $request->user()->id;
        $badges = $this->gamificationService->getBadgesCollection($userId);
        return $this->successResponse($badges, 'Badges collection retrieved successfully');
    }

    /**
     * GET /api/v1/gamification/xp-history OR /api/v1/gamification/history
     */
    public function getXpHistory(Request $request)
    {
        $history = [
            ['title' => 'Presensi Tepat Waktu', 'xp' => 15, 'timestamp' => 'Hari ini, 06:55 WIB', 'iconEmoji' => 'location'],
            ['title' => 'Tugas Matematika Selesai', 'xp' => 35, 'timestamp' => 'Kemarin, 14:20 WIB', 'iconEmoji' => 'ruler'],
            ['title' => 'Setoran Tahsin Al-Qur\'an', 'xp' => 50, 'timestamp' => 'Kemarin, 08:30 WIB', 'iconEmoji' => 'book'],
            ['title' => 'Lulus Kuis Fisika', 'xp' => 40, 'timestamp' => '2 hari yang lalu', 'iconEmoji' => 'bolt'],
        ];

        return $this->successResponse($history, 'XP history retrieved successfully');
    }

    /**
     * GET /api/v1/gamification/streak-calendar OR /api/v1/gamification/streak
     */
    public function getStreakCalendar(Request $request)
    {
        $userId = $request->user()->id;
        $student = \App\Models\Student::where('user_id', $userId)->first();
        $studentId = $student ? $student->id : $userId;
        $calendar = $this->gamificationService->getStreakCalendar($studentId);
        return $this->successResponse($calendar, 'Streak calendar retrieved successfully');
    }
}
