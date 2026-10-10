<?php

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    // Make sure we have an active academic year
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
});

test('admin can access dashboard and view admin stats', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('todayAttendanceRate');
    $response->assertViewHas('collectionRate');
    $response->assertViewHas('journalFilledRate');
    $response->assertViewHas('averageGrades');
});

test('guru can access dashboard and view teacher stats', function () {
    $user = User::factory()->create(['role' => 'guru']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('schedules');
    $response->assertViewHas('pendingAttendanceAlerts');
    $response->assertViewHas('journalTodayFilled');
});

test('student can access dashboard and view student stats', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('studentSchedulesToday');
    $response->assertViewHas('mutabaahItems');
    // FASE 119: XP and levels were removed with gamification.
    $response->assertViewMissing('studentLevel');
    $response->assertViewMissing('recentBadges');
});

test('parent can access dashboard and view parent stats', function () {
    $user = User::factory()->create(['role' => 'parent']);
    $student = Student::factory()->create(['parent_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('children');
    $response->assertViewHas('upcomingSchoolEvents');
});
