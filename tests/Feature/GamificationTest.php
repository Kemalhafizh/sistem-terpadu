<?php

use App\Models\AchievementDefinition;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentXpLog;
use App\Models\User;
use App\Services\GamificationService;

beforeEach(function () {
    // Clean tables before each test
    StudentAchievement::truncate();
    StudentXpLog::truncate();
    AchievementDefinition::truncate();
    Student::query()->update(['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0]);
});

test('unauthenticated guests are blocked from all gamification pages', function () {
    $this->get(route('gamification.leaderboard'))->assertRedirect('/login');
    $this->get(route('gamification.my-progress'))->assertRedirect('/login');
    $this->get(route('gamification.achievements'))->assertRedirect('/login');
});

test('teachers or admins can access leaderboard but not student progress dashboards', function () {
    $teacher = User::factory()->create(['role' => 'guru']);

    // Can access leaderboard
    $response = $this->actingAs($teacher)->get(route('gamification.leaderboard'));
    $response->assertStatus(200);

    // Blocked from my-progress
    $response = $this->actingAs($teacher)->get(route('gamification.my-progress'));
    $response->assertRedirect('/dashboard');

    // Blocked from achievements
    $response = $this->actingAs($teacher)->get(route('gamification.achievements'));
    $response->assertRedirect('/dashboard');
});

test('students can access all three gamification pages successfully', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);

    $this->actingAs($studentUser)->get(route('gamification.leaderboard'))->assertStatus(200);
    $this->actingAs($studentUser)->get(route('gamification.my-progress'))->assertStatus(200);
    $this->actingAs($studentUser)->get(route('gamification.achievements'))->assertStatus(200);
});

test('XP awarding service increments student XP, creates transaction log, and computes level correctly', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);

    $service = app(GamificationService::class);
    $service->awardXp($student, 150, 'assignment', 'Menyelesaikan tugas E-Learning.');

    $student = $student->fresh();

    // Check XP increment
    $this->assertEquals(150, $student->total_xp);

    // Check level calculation: 1 + floor(total_xp / 100) = 1 + floor(150 / 100) = 2
    $this->assertEquals(2, $student->level);

    // Check progress percentage: (150 - 100) = 50% progress to level 3
    $this->assertEquals(50.0, $student->xp_progress_percent);

    // Check database log
    $this->assertDatabaseHas('student_xp_logs', [
        'student_id' => $student->id,
        'xp_amount' => 150,
        'source' => 'assignment',
    ]);
});

test('attendance streak increments on presence status H and resets on absence status A', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);

    $service = app(GamificationService::class);

    // 1. Presence 'H' - should increment streak and award XP
    $service->updateAttendanceStreak($student, 'H');
    $student = $student->fresh();

    $this->assertEquals(1, $student->current_streak);
    $this->assertEquals(1, $student->longest_streak);
    $this->assertEquals(10, $student->total_xp);

    // 2. Presence 'H' again
    $service->updateAttendanceStreak($student, 'H');
    $student = $student->fresh();

    $this->assertEquals(2, $student->current_streak);
    $this->assertEquals(2, $student->longest_streak);
    $this->assertEquals(20, $student->total_xp);

    // 3. Absence 'A' - should reset streak to 0 (no XP awarded)
    $service->updateAttendanceStreak($student, 'A');
    $student = $student->fresh();

    $this->assertEquals(0, $student->current_streak);
    $this->assertEquals(2, $student->longest_streak); // Longest streak preserved
    $this->assertEquals(20, $student->total_xp); // Total XP remains unchanged
});

test('achievements are automatically awarded when criteria thresholds are met', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);

    // Create an auto-badge definition for perfect week (streak >= 5)
    $badge = AchievementDefinition::create([
        'code' => 'perfect_week',
        'name' => 'Minggu Sempurna ⭐',
        'description' => 'Streak presensi 5 hari.',
        'icon' => '⭐',
        'color' => '#10b981',
        'category' => 'kehadiran',
        'rarity' => 'common',
        'xp_reward' => 15,
        'criteria' => ['type' => 'attendance_streak', 'value' => 5],
        'is_auto' => true,
        'is_active' => true,
    ]);

    $service = app(GamificationService::class);

    // Trigger attendance streak to 5
    for ($i = 0; $i < 5; $i++) {
        $service->updateAttendanceStreak($student, 'H');
        $student = $student->fresh();
    }

    // Assert student earned the badge in database
    $this->assertDatabaseHas('student_achievements', [
        'student_id' => $student->id,
        'achievement_id' => $badge->id,
    ]);

    // Verify badge reward XP is added (5 presences = 50 XP, badge award = 15 XP. Total = 65 XP)
    $this->assertEquals(65, $student->fresh()->total_xp);
});

test('leaderboard returns ranked students sorted by total XP and longest streak', function () {
    $class = Classroom::factory()->create();

    $s1_user = User::factory()->create(['role' => 'student']);
    $s1 = Student::factory()->create(['user_id' => $s1_user->id, 'classroom_id' => $class->id, 'total_xp' => 300, 'longest_streak' => 10]);

    $s2_user = User::factory()->create(['role' => 'student']);
    $s2 = Student::factory()->create(['user_id' => $s2_user->id, 'classroom_id' => $class->id, 'total_xp' => 500, 'longest_streak' => 5]);

    $s3_user = User::factory()->create(['role' => 'student']);
    $s3 = Student::factory()->create(['user_id' => $s3_user->id, 'classroom_id' => $class->id, 'total_xp' => 300, 'longest_streak' => 15]);

    $service = app(GamificationService::class);
    $leaderboard = $service->getLeaderboard('all_time', $class->id);

    // Expected order:
    // 1st: s2 (500 XP)
    // 2nd: s3 (300 XP, streak 15) - tie-breaker
    // 3rd: s1 (300 XP, streak 10)

    $this->assertCount(3, $leaderboard);
    $this->assertEquals($s2->id, $leaderboard[0]->id);
    $this->assertEquals($s3->id, $leaderboard[1]->id);
    $this->assertEquals($s1->id, $leaderboard[2]->id);
});
