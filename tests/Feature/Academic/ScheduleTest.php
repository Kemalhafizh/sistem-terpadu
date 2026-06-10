<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchedulePreset;
use App\Models\ScheduleTime;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    // 1. Setup Users
    $this->guru = User::factory()->create(['role' => 'guru', 'name' => 'Ahmad Guru']);
    $this->guru2 = User::factory()->create(['role' => 'guru', 'name' => 'Siti Guru']);
    $this->wakakur = User::factory()->create(['role' => 'waka_kurikulum', 'name' => 'Bambang Wakakur']);
    $this->student = User::factory()->create(['role' => 'student']);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);
    $this->classroom1 = Classroom::factory()->create(['academic_year' => $this->academicYear->year, 'name' => 'Kelas X-A']);
    $this->classroom2 = Classroom::factory()->create(['academic_year' => $this->academicYear->year, 'name' => 'Kelas X-B']);
    $this->subject1 = Subject::factory()->create(['name' => 'Fisika']);
    $this->subject2 = Subject::factory()->create(['name' => 'Matematika']);

    // Setup Rooms
    $this->room1 = Room::create(['name' => 'Laboratorium Fisika', 'location' => 'Gedung B Lt. 2']);
    $this->room2 = Room::create(['name' => 'Kelas 10-A', 'location' => 'Gedung A Lt. 1']);

    // Setup Presets
    $this->preset = SchedulePreset::create(['name' => 'Preset Normal', 'is_active' => true]);
    $this->time1 = ScheduleTime::create([
        'schedule_preset_id' => $this->preset->id,
        'session_number' => 1,
        'start_time' => '07:00:00',
        'end_time' => '07:45:00',
    ]);
    $this->time2 = ScheduleTime::create([
        'schedule_preset_id' => $this->preset->id,
        'session_number' => 2,
        'start_time' => '07:45:00',
        'end_time' => '08:30:00',
    ]);
});

// =========================================================
// 🔒 AUTHORIZATION TESTS
// =========================================================

test('authorized users can access schedule views and management', function () {
    $this->actingAs($this->wakakur);

    $this->get(route('schedules.grid'))->assertStatus(200);
    $this->get(route('schedules.presets.index'))->assertStatus(200);
    $this->get(route('schedules.teacher'))->assertStatus(200);
    $this->get(route('schedules.room'))->assertStatus(200);
    $this->get(route('schedules.conflicts'))->assertStatus(200);
});

test('unauthorized users cannot access schedule views and management', function () {
    $this->actingAs($this->student);

    $this->get(route('schedules.grid'))->assertStatus(403);
    $this->get(route('schedules.presets.index'))->assertStatus(403);
    $this->get(route('schedules.teacher'))->assertStatus(403);
    $this->get(route('schedules.room'))->assertStatus(403);
    $this->get(route('schedules.conflicts'))->assertStatus(403);
});

// =========================================================
// 📅 PLOTTING & 3-WAY CONFLICT CHECKING TESTS
// =========================================================

test('can create a valid schedule slot', function () {
    $this->actingAs($this->wakakur);

    $response = $this->post(route('schedules.store'), [
        'classroom_id' => $this->classroom1->id,
        'subject_id' => $this->subject1->id,
        'teacher_id' => $this->guru->id,
        'room_id' => $this->room1->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('schedules', [
        'classroom_id' => $this->classroom1->id,
        'subject_id' => $this->subject1->id,
        'teacher_id' => $this->guru->id,
        'room_id' => $this->room1->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);
});

test('prevents creating schedule with teacher conflict', function () {
    $this->actingAs($this->wakakur);

    // Create initial schedule for guru in classroom1
    Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom1->id,
        'subject_id' => $this->subject1->id,
        'teacher_id' => $this->guru->id,
        'room_id' => $this->room1->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    // Attempt to schedule the same guru at the same session in classroom2
    $response = $this->post(route('schedules.store'), [
        'classroom_id' => $this->classroom2->id,
        'subject_id' => $this->subject2->id,
        'teacher_id' => $this->guru->id,
        'room_id' => $this->room2->id,
        'day' => 'Senin',
        'session_start' => 2, // Overlaps session 1-2
        'session_end' => 3,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertStringContainsString('Konflik Guru', session('error'));
});

test('prevents creating schedule with classroom conflict', function () {
    $this->actingAs($this->wakakur);

    // Create initial schedule for classroom1
    Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom1->id,
        'subject_id' => $this->subject1->id,
        'teacher_id' => $this->guru->id,
        'room_id' => $this->room1->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    // Attempt to schedule another class in classroom1 at overlapping session
    $response = $this->post(route('schedules.store'), [
        'classroom_id' => $this->classroom1->id,
        'subject_id' => $this->subject2->id,
        'teacher_id' => $this->guru2->id,
        'room_id' => $this->room2->id,
        'day' => 'Senin',
        'session_start' => 2,
        'session_end' => 3,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertStringContainsString('Konflik Kelas', session('error'));
});

test('prevents creating schedule with room conflict', function () {
    $this->actingAs($this->wakakur);

    // Create initial schedule using room1
    Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom1->id,
        'subject_id' => $this->subject1->id,
        'teacher_id' => $this->guru->id,
        'room_id' => $this->room1->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    // Attempt to schedule another class in the same room1 at overlapping session
    $response = $this->post(route('schedules.store'), [
        'classroom_id' => $this->classroom2->id,
        'subject_id' => $this->subject2->id,
        'teacher_id' => $this->guru2->id,
        'room_id' => $this->room1->id,
        'day' => 'Senin',
        'session_start' => 2,
        'session_end' => 3,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertStringContainsString('Konflik Ruangan', session('error'));
});

// =========================================================
// 🔍 GRID VIEWS & AUDIT TESTS
// =========================================================

test('teacher view filters schedules correctly', function () {
    $this->actingAs($this->wakakur);

    // Create schedule for guru
    Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom1->id,
        'subject_id' => $this->subject1->id,
        'teacher_id' => $this->guru->id,
        'room_id' => $this->room1->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    $response = $this->get(route('schedules.teacher', ['teacher_id' => $this->guru->id]));
    $response->assertStatus(200);
    $response->assertViewHas('selectedTeacher', function ($teacher) {
        return $teacher->id === $this->guru->id;
    });
});

test('room view filters schedules correctly', function () {
    $this->actingAs($this->wakakur);

    // Create schedule in room1
    Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom1->id,
        'subject_id' => $this->subject1->id,
        'teacher_id' => $this->guru->id,
        'room_id' => $this->room1->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    $response = $this->get(route('schedules.room', ['room_id' => $this->room1->id]));
    $response->assertStatus(200);
    $response->assertViewHas('selectedRoom', function ($room) {
        return $room->id === $this->room1->id;
    });
});

test('conflict audit dashboard lists all conflicts in database', function () {
    $this->actingAs($this->wakakur);

    // Force conflict in database bypassing validations
    // Teacher conflict: guru teaches classroom1 and classroom2 at the same time
    Schedule::insert([
        [
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom1->id,
            'subject_id' => $this->subject1->id,
            'teacher_id' => $this->guru->id,
            'room_id' => $this->room1->id,
            'day' => 'Senin',
            'session_start' => 1,
            'session_end' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom2->id,
            'subject_id' => $this->subject2->id,
            'teacher_id' => $this->guru->id,
            'room_id' => $this->room2->id,
            'day' => 'Senin',
            'session_start' => 1,
            'session_end' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);

    $response = $this->get(route('schedules.conflicts'));
    $response->assertStatus(200);
    $response->assertViewHas('conflicts');
    
    $conflicts = $response->viewData('conflicts');
    $this->assertCount(1, $conflicts);
    $this->assertEquals('Guru', $conflicts[0]['type']);
});

// =========================================================
// 🖨️ PDF EXPORT TESTS
// =========================================================

test('authorized users can download class schedule as landscape PDF', function () {
    $this->actingAs($this->wakakur);

    $response = $this->get(route('schedules.print.class', $this->classroom1));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('authorized users can download teacher schedule as landscape PDF', function () {
    $this->actingAs($this->wakakur);

    $response = $this->get(route('schedules.print.teacher', $this->guru));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('authorized users can download master schedule as landscape PDF', function () {
    $this->actingAs($this->wakakur);

    $response = $this->get(route('schedules.print.master'));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
