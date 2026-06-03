<?php

/** @var TestCase|stdClass $this */

use App\Models\Announcement;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->parentUser = User::factory()->create(['role' => 'parent']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'parent_id' => $this->parentUser->id,
    ]);
    $this->token = $this->studentUser->createToken('test')->plainTextToken;
    $this->parentToken = $this->parentUser->createToken('test')->plainTextToken;
});

// ── GET STUDENTS ──────────────────────────────────────────────────────

test('student can get their own student data', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/students');

    $response->assertStatus(200);
    expect($response->json('success'))->toBeTrue();
});

test('unauthenticated request to students returns 401', function () {
    $this->getJson('/api/v1/students')->assertStatus(401);
});

// ── GET ATTENDANCES ───────────────────────────────────────────────────

test('student can get their attendance data', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/students/{$this->student->uuid}/attendances");

    $response->assertStatus(200);
    expect($response->json('success'))->toBeTrue();
});

test('student cannot access another students attendance', function () {
    $otherStudent = Student::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/students/{$otherStudent->uuid}/attendances");

    $response->assertStatus(404);
});

// ── GET GRADES ────────────────────────────────────────────────────────

test('student can get their grade data', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/students/{$this->student->uuid}/grades");

    $response->assertStatus(200);
    expect($response->json('success'))->toBeTrue();
});

// ── GET BILLINGS ──────────────────────────────────────────────────────

test('student can get their billing data', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/students/{$this->student->uuid}/billings");

    $response->assertStatus(200);
    expect($response->json('success'))->toBeTrue();
});

// ── GET ANNOUNCEMENTS ─────────────────────────────────────────────────

test('student can get active announcements', function () {
    Announcement::factory()->create([
        'is_active' => true,
        'target_role' => null,
        'created_by' => User::factory()->create(['role' => 'admin'])->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/announcements');

    $response->assertStatus(200);
    expect($response->json('success'))->toBeTrue();
});

// ── PARENT ACCESS ─────────────────────────────────────────────────────

test('parent can access their childs data', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
        ->getJson("/api/v1/students/{$this->student->uuid}/grades");

    $response->assertStatus(200);
    expect($response->json('success'))->toBeTrue();
});
