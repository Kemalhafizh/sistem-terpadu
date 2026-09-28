<?php

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->parent = User::factory()->create(['role' => 'wali_murid', 'must_change_password' => false]);
    $classroom = Classroom::factory()->create();
    $make = fn (string $name) => Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student', 'name' => $name])->id,
        'parent_id' => $this->parent->id,
        'classroom_id' => $classroom->id,
    ]);
    $this->first = $make('Anak Pertama');
    $this->second = $make('Anak Kedua');
    $this->stranger = Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student'])->id,
        'classroom_id' => $classroom->id,
    ]);
});

test('a parent can pick any of their children by uuid, the only id the app has', function () {
    Sanctum::actingAs($this->parent);

    foreach (['feed', 'digest', 'benchmark'] as $endpoint) {
        $this->getJson("/api/v1/parent/child/{$this->second->uuid}/{$endpoint}")->assertOk();
        $this->getJson("/api/v1/parent/child/{$this->stranger->uuid}/{$endpoint}")->assertForbidden();
    }
    $this->getJson('/api/v1/parent/child/not-a-real-uuid/feed')->assertNotFound();
});

test('numeric child ids keep working', function () {
    Sanctum::actingAs($this->parent);

    $this->getJson("/api/v1/parent/child/{$this->second->id}/feed")->assertOk();
    $this->getJson("/api/v1/parent/child/{$this->stranger->id}/feed")->assertForbidden();
});

test('a student account without a student profile never gets student #1 data', function () {
    // The old fallback resolved a missing profile to students.id = 1.
    Sanctum::actingAs(User::factory()->create(['role' => 'siswa', 'must_change_password' => false]));

    $this->getJson('/api/v1/parent/feed')->assertNotFound();
    $this->getJson('/api/v1/parent/weekly-digest')->assertNotFound();
});

test('every parent role spelling reaches child progress, for the chosen child', function () {
    foreach (['parent', 'orang_tua', 'orangtua', 'wali_murid'] as $role) {
        $this->parent->update(['role' => $role]);
        Sanctum::actingAs($this->parent->fresh());

        $this->getJson('/api/v1/analytics/parent/child-progress')->assertOk();
        $this->getJson("/api/v1/analytics/parent/child-progress?student_id={$this->second->uuid}")->assertOk();
        $this->getJson("/api/v1/analytics/parent/child-progress?student_id={$this->stranger->uuid}")->assertNotFound();
    }
});
