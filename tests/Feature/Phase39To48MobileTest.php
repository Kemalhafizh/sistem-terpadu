<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
});

test('user can fetch contextual home payload', function () {
    $response = $this->actingAs($this->user)->getJson('/api/v1/home/contextual');
    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => [
                     'context' => ['timeOfDay', 'isExamSeason', 'activeEvent', 'hijriDate'],
                     'hero' => ['type', 'title', 'subtitle', 'gradientStart', 'gradientEnd', 'iconEmoji', 'actionLabel', 'actionRoute'],
                     'quickActions',
                     'suggestions',
                     'gamification' => ['xpToday', 'totalXp', 'level', 'streakDays']
                 ]
             ]);
});

test('user can fetch and dismiss smart suggestions', function () {
    $response = $this->actingAs($this->user)->getJson('/api/v1/home/suggestions');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);

    $dismiss = $this->actingAs($this->user)->postJson('/api/v1/home/suggestions/sugg_1/dismiss');
    $dismiss->assertStatus(200);
});

test('user can fetch gamification profile, leaderboard, and badges', function () {
    $profile = $this->actingAs($this->user)->getJson('/api/v1/gamification/profile');
    $profile->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['totalXp', 'level', 'levelTitle', 'xpToNextLevel', 'streakDays', 'longestStreak', 'badgesEarned', 'monthlyRank']]);

    $leaderboard = $this->actingAs($this->user)->getJson('/api/v1/gamification/leaderboard');
    $leaderboard->assertStatus(200)
                ->assertJsonStructure(['success', 'data']);

    $badges = $this->actingAs($this->user)->getJson('/api/v1/gamification/badges');
    $badges->assertStatus(200)
           ->assertJsonStructure(['success', 'data' => ['earned', 'locked']]);
});

test('user can get and update notification preferences', function () {
    $getPref = $this->actingAs($this->user)->getJson('/api/v1/notifications/preferences');
    $getPref->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['examNotifsEnabled', 'assignmentNotifsEnabled', 'digestMode']]);

    $updatePref = $this->actingAs($this->user)->putJson('/api/v1/notifications/preferences', [
        'examNotifsEnabled' => true,
        'digestMode' => 'morning'
    ]);
    $updatePref->assertStatus(200);
});

test('parent can view child activity feed, weekly digest, and comparisons', function () {
    $feed = $this->actingAs($this->user)->getJson('/api/v1/parent/feed');
    $feed->assertStatus(200)
         ->assertJsonStructure(['success', 'data']);

    $digest = $this->actingAs($this->user)->getJson('/api/v1/parent/weekly-digest');
    $digest->assertStatus(200)
           ->assertJsonStructure(['success', 'data' => ['weekStart', 'attendancePercentage', 'averageGrade', 'ibadahScore']]);

    $comparison = $this->actingAs($this->user)->getJson('/api/v1/parent/comparison');
    $comparison->assertStatus(200)
               ->assertJsonStructure(['success', 'data']);
});

test('user can get ai tutor suggestions', function () {
    $aiSuggestions = $this->actingAs($this->user)->getJson('/api/v1/ai/tutor/suggestions');
    $aiSuggestions->assertStatus(200)
                  ->assertJsonStructure(['success', 'data']);
});
