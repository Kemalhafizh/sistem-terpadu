<?php

use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
});

test('Fase 99: Contextual home feed, suggestions and dismissal work correctly', function () {
    // 1. Contextual Home Payload
    $homeResp = $this->actingAs($this->user)->getJson('/api/v1/home/contextual');
    $homeResp->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'context' => ['timeOfDay', 'isExamSeason', 'activeEvent', 'hijriDate'],
                'hero' => ['type', 'title', 'subtitle', 'gradientStart', 'gradientEnd', 'actionLabel', 'actionRoute'],
                'quickActions',
                'suggestions',
                'gamification' => ['xpToday', 'totalXp', 'level', 'streakDays'],
            ],
        ]);

    // 2. Smart suggestions
    $suggResp = $this->actingAs($this->user)->getJson('/api/v1/home/suggestions');
    $suggResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);

    // 3. Dismiss suggestion
    $dismissResp = $this->actingAs($this->user)->postJson('/api/v1/home/dismiss-suggestion', [
        'suggestionId' => 'sugg_1',
    ]);
    $dismissResp->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('Fase 100: Micro-interactions and animation preferences work correctly', function () {
    // 1. Get animation config
    $animResp = $this->actingAs($this->user)->getJson('/api/v1/config/animations');
    $animResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['reduce_motion', 'haptic_level', 'skeleton_loading', 'spring_animations']]);

    // 2. Update animation preferences
    $updateResp = $this->actingAs($this->user)->putJson('/api/v1/config/animations/preferences', [
        'reduce_motion' => true,
        'haptic_feedback_level' => 'off',
        'enable_glassmorphism' => false,
    ]);
    $updateResp->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('Fase 101: Gamification profile, leaderboard, badges and streak calendar work correctly', function () {
    // 1. Profile
    $profileResp = $this->actingAs($this->user)->getJson('/api/v1/gamification/profile');
    $profileResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['totalXp', 'level', 'levelTitle', 'xpToNextLevel', 'streakDays', 'longestStreak', 'badgesEarned', 'monthlyRank']]);

    // 2. Leaderboard
    $leaderboardResp = $this->actingAs($this->user)->getJson('/api/v1/gamification/leaderboard');
    $leaderboardResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);

    // 3. Badges
    $badgesResp = $this->actingAs($this->user)->getJson('/api/v1/gamification/badges');
    $badgesResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['earned', 'locked']]);

    // 4. Streak calendar
    $streakResp = $this->actingAs($this->user)->getJson('/api/v1/gamification/streak-calendar');
    $streakResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);

    // 5. XP History
    $historyResp = $this->actingAs($this->user)->getJson('/api/v1/gamification/xp-history');
    $historyResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('Fase 102: Smart notification preferences and digest work correctly', function () {
    // 1. Get preferences
    $prefResp = $this->actingAs($this->user)->getJson('/api/v1/notifications/preferences');
    $prefResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['examNotifsEnabled', 'assignmentNotifsEnabled', 'digestMode', 'quietHoursStart', 'quietHoursEnd']]);

    // 2. Update preferences
    $updateResp = $this->actingAs($this->user)->putJson('/api/v1/notifications/preferences', [
        'examNotifsEnabled' => true,
        'digestMode' => 'evening',
        'quietHoursStart' => '22:00',
        'quietHoursEnd' => '05:00',
    ]);
    $updateResp->assertStatus(200)
        ->assertJson(['success' => true]);

    // 3. Digest summary
    $digestResp = $this->actingAs($this->user)->getJson('/api/v1/notifications/digest');
    $digestResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['has_digest', 'count', 'summary', 'items']]);
});

test('Fase 104: Advanced data visualization endpoints return complete metrics', function () {
    // 1. Heatmap attendance
    $heatmapResp = $this->actingAs($this->user)->getJson('/api/v1/viz/heatmap/attendance');
    $heatmapResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);

    // 2. Grade distribution
    $distResp = $this->actingAs($this->user)->getJson('/api/v1/viz/distribution/grades/1');
    $distResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['exam_id', 'count', 'mean', 'median', 'standard_deviation', 'buckets']]);

    // 3. Radar student
    $radarResp = $this->actingAs($this->user)->getJson('/api/v1/viz/radar/student/1');
    $radarResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['student_id', 'dimensions', 'benchmark_average']]);

    // 4. Trend student
    $trendResp = $this->actingAs($this->user)->getJson('/api/v1/viz/trend/student/1');
    $trendResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['student_id', 'metric', 'points', 'months']]);

    // 5. Class comparison
    $compResp = $this->actingAs($this->user)->getJson('/api/v1/viz/comparison/classes');
    $compResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);

    // 6. Live counters
    $countersResp = $this->actingAs($this->user)->getJson('/api/v1/viz/counters/live');
    $countersResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['active_students_online', 'attendance_today_percentage', 'active_cbt_exams', 'unpaid_billings_count']]);
});

test('Fase 105: Offline-first sync delta, batch processing and version check work', function () {
    // 1. Delta
    $deltaResp = $this->actingAs($this->user)->getJson('/api/v1/sync/delta?since=1700000000');
    $deltaResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['sync_timestamp', 'delta_count', 'changes', 'tombstones']]);

    // 2. Process Batch
    $batchPayload = [
        'clientId' => 'test-device-uuid',
        'actions' => [
            [
                'id' => 'act_101',
                'type' => 'mutabaah',
                'data' => [
                    'student_id' => 1,
                    'date' => '2026-12-15',
                    'subuh' => true,
                ],
                'timestamp' => 1700000000,
            ],
        ],
    ];
    $batchResp = $this->actingAs($this->user)->postJson('/api/v1/sync/batch', $batchPayload);
    $batchResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['processed', 'results']]);

    // 3. Version Check
    $verResp = $this->actingAs($this->user)->getJson('/api/v1/sync/check-version');
    $verResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['schema_version', 'min_supported_mobile_version']]);
});

test('Fase 106: Parent portal activity feed, weekly digest, benchmarks and actions work', function () {
    // A student sees only their own profile; an account without one gets nothing
    // (it used to fall back to students.id = 1).
    $own = Student::factory()->create(['user_id' => $this->user->id])->id;

    // 1. Activity Feed
    $feedResp = $this->actingAs($this->user)->getJson("/api/v1/parent/child/{$own}/feed");
    $feedResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);

    // 2. Weekly Digest
    $digestResp = $this->actingAs($this->user)->getJson("/api/v1/parent/child/{$own}/digest");
    $digestResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['weekStart', 'attendancePercentage', 'averageGrade', 'ibadahScore', 'highlights']]);

    // 3. Class Benchmark
    $benchResp = $this->actingAs($this->user)->getJson("/api/v1/parent/child/{$own}/benchmark");
    $benchResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);

    // 4. Quick Actions
    $actionsResp = $this->actingAs($this->user)->getJson('/api/v1/parent/actions');
    $actionsResp->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('Fase 107: Progressive Web App manifest, service worker and subscribe work', function () {
    // 1. Manifest
    $manifestResp = $this->get('/manifest.json');
    $manifestResp->assertStatus(200);
    expect($manifestResp->headers->get('Content-Type'))->toContain('application/manifest+json');
    $manifestResp->assertJsonStructure(['name', 'short_name', 'start_url', 'display', 'icons']);

    // 2. Service Worker script
    $swResp = $this->get('/sw.js');
    $swResp->assertStatus(200);
    expect($swResp->headers->get('Content-Type'))->toContain('javascript');

    // 3. Push subscribe
    $subResp = $this->actingAs($this->user)->postJson('/pwa/subscribe', [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-token-123',
        'keys' => [
            'p256dh' => 'mock-p256dh-key',
            'auth' => 'mock-auth-secret',
        ],
    ]);
    $subResp->assertStatus(200)
        ->assertJson(['success' => true]);
});
