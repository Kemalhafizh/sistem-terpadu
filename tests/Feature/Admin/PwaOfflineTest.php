<?php

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\File;

test('service-worker.js file exists in public directory', function () {
    $this->assertTrue(File::exists(public_path('service-worker.js')));
});

test('offline-queue.js file exists in public directory', function () {
    $this->assertTrue(File::exists(public_path('offline-queue.js')));
});

test('app layout loads offline-queue.js script', function () {
    $user = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('offline-queue.js');
});

test('attendance create form has offline sync data attributes', function () {
    $user = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);

    $classroom = Classroom::create([
        'name' => 'X-A',
        'grade_level' => 'X',
        'major' => 'MIPA',
        'academic_year' => '2026/2027',
    ]);

    $response = $this->actingAs($user)->get(route('attendances.create', ['classroom_id' => $classroom->id]));
    $response->assertOk();
    $response->assertSee('id="attendance-form"', false);
    $response->assertSee('data-offline-sync="true"', false);
    $response->assertSee('data-type-name="Presensi"', false);
});

test('teaching journal create form has offline sync data attributes', function () {
    $user = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);

    $response = $this->actingAs($user)->get(route('teaching-journals.create'));
    $response->assertOk();
    $response->assertSee('id="teaching-journal-form"', false);
    $response->assertSee('data-offline-sync="true"', false);
    $response->assertSee('data-type-name="Jurnal Mengajar"', false);
});
