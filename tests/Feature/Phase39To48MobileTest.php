<?php

use App\Models\Student;
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
            ],
        ]);
});

test('user can fetch and dismiss smart suggestions', function () {
    $response = $this->actingAs($this->user)->getJson('/api/v1/home/suggestions');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);

    $dismiss = $this->actingAs($this->user)->postJson('/api/v1/home/suggestions/sugg_1/dismiss');
    $dismiss->assertStatus(200);
});

test('user can get and update notification preferences', function () {
    $getPref = $this->actingAs($this->user)->getJson('/api/v1/notifications/preferences');
    $getPref->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['examNotifsEnabled', 'assignmentNotifsEnabled', 'digestMode']]);

    $updatePref = $this->actingAs($this->user)->putJson('/api/v1/notifications/preferences', [
        'examNotifsEnabled' => true,
        'digestMode' => 'morning',
    ]);
    $updatePref->assertStatus(200);
});

test('parent can view child activity feed, weekly digest, and comparisons', function () {
    // A real parent with a linked child: the old fallback to students.id = 1 is gone.
    $parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    Student::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id]);
    $this->user = $parent;

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
