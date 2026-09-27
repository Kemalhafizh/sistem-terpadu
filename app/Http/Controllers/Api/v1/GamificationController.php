<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Student;
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
        // No time-windowed XP aggregate exists in the schema yet — every
        // scope is really all-time. Accepted but not applied, rather than
        // silently pretending a "monthly" cut actually happened.
        $user = $request->user();

        $query = Student::with(['user', 'classroom'])
            ->orderByDesc('total_xp');

        if ($scope === 'class') {
            $requester = Student::where('user_id', $user->id)->first();
            if ($requester?->classroom_id) {
                $query->where('classroom_id', $requester->classroom_id);
            }
        }

        $leaderboard = $query->limit(20)->get()->values()->map(function ($student, $index) use ($user) {
            return [
                'rank' => $index + 1,
                'name' => $student->name,
                'className' => $student->classroom?->name ?? '-',
                'xp' => $student->total_xp,
                'isCurrentUser' => (int) $student->user_id === (int) $user->id,
            ];
        });

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
        $student = Student::where('user_id', $userId)->first();
        $studentId = $student ? $student->id : $userId;
        $calendar = $this->gamificationService->getStreakCalendar($studentId);

        return $this->successResponse($calendar, 'Streak calendar retrieved successfully');
    }
}
