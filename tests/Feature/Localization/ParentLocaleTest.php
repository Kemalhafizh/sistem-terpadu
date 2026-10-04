<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/*
 * A parent who picked English or Arabic used to get the child's feed, the
 * weekly digest and the shortcuts in Indonesian. The app also coloured feed
 * events by looking for "Hadir" or "prestasi" in the title, which breaks as
 * soon as the title is translated, so every event now carries a fixed code.
 */

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-09-30 10:00:00')); // a Wednesday
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $classroom = Classroom::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Fisika']);
    $teacher = User::factory()->create(['role' => 'guru']);
    $this->parentFor = function (string $locale) use ($year, $classroom, $subject, $teacher) {
        $parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false, 'preferred_locale' => $locale]);
        $child = Student::factory()->create([
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'parent_id' => $parent->id,
            'classroom_id' => $classroom->id,
        ]);
        foreach (['2026-09-29' => 'H', '2026-09-30' => 'S'] as $date => $status) {
            Attendance::create([
                'student_id' => $child->id, 'academic_year_id' => $year->id, 'classroom_id' => $classroom->id,
                'date' => $date, 'status' => $status, 'recorded_by' => $teacher->id,
            ]);
        }
        Grade::create([
            'student_id' => $child->id, 'subject_id' => $subject->id, 'academic_year_id' => $year->id,
            'type' => 'uh', 'score' => 88.5, 'recorded_by' => $teacher->id,
        ]);
        StudentPointLog::create([
            'student_id' => $child->id, 'type' => 'reward', 'points' => 10,
            'description' => 'Juara lomba', 'created_by' => $teacher->id,
        ]);

        return [$parent, $child];
    };
});

afterEach(fn () => Carbon::setTestNow());

test('the feed is in english for a parent who chose english, with codes that never change', function () {
    [$parent] = ($this->parentFor)('en');
    Sanctum::actingAs($parent);

    $feed = collect($this->getJson('/api/v1/parent/feed')->assertOk()->json('data'))->keyBy('code');

    expect($feed->keys()->sort()->values()->all())->toBe(['grade', 'present', 'reward', 'sick'])
        ->and($feed['present']['title'])->toBe('Attendance: Present')
        ->and($feed['sick']['title'])->toBe('Attendance: Sick')
        ->and($feed['grade']['title'])->toBe('Grade UH Fisika')
        ->and($feed['grade']['description'])->toBe('Score 88.5')
        ->and($feed['reward']['title'])->toBe('Achievement points +10')
        // What a teacher typed stays as typed.
        ->and($feed['reward']['description'])->toBe('Juara lomba');

    $digest = $this->getJson('/api/v1/parent/weekly-digest')->assertOk()->json('data');
    expect($digest['highlights'])->toContain('Present 1 of 2 recorded attendances.', 'Absent: 1 sick.', 'New grades: 1.', 'Achievement points +10.');

    $actions = collect($this->getJson('/api/v1/parent/actions')->assertOk()->json('data'))->keyBy('id');
    expect($actions['chat_teacher']['title'])->toBe('Message the homeroom teacher')
        ->and($actions['view_rapor']['title'])->toBe('Report card');
});

test('the feed is in arabic for a parent who chose arabic', function () {
    [$parent] = ($this->parentFor)('ar');
    Sanctum::actingAs($parent);

    $feed = collect($this->getJson('/api/v1/parent/feed')->assertOk()->json('data'))->keyBy('code');
    expect($feed['present']['title'])->toBe('الحضور: حاضر')
        ->and($feed['reward']['title'])->toBe('نقاط التميز +10')
        ->and($feed['grade']['description'])->toBe('الدرجة 88.5');
});

test('indonesian stays as it was, with an indonesian decimal comma', function () {
    [$parent] = ($this->parentFor)('id');
    Sanctum::actingAs($parent);

    $feed = collect($this->getJson('/api/v1/parent/feed')->assertOk()->json('data'))->keyBy('code');
    expect($feed['present']['title'])->toBe('Presensi: Hadir')
        ->and($feed['grade']['description'])->toBe('Nilai 88,5')
        ->and($feed['reward']['title'])->toBe('Poin prestasi +10');
});

test('the child\'s attendance list labels each status in the parent\'s language and keeps the code', function () {
    [$parent, $child] = ($this->parentFor)('ar');
    Sanctum::actingAs($parent);

    $logs = collect($this->getJson("/api/v1/parent/child/{$child->uuid}/attendance")->assertOk()->json('data'))->keyBy('status');
    expect($logs['H']['status_label'])->toBe('حاضر')
        ->and($logs['S']['status_label'])->toBe('مريض');
});

test('another family\'s child is refused in the parent\'s language', function () {
    [, $otherChild] = ($this->parentFor)('id');
    [$parent] = ($this->parentFor)('en');
    Sanctum::actingAs($parent);

    $this->getJson("/api/v1/parent/child/{$otherChild->uuid}/feed")
        ->assertForbidden()
        ->assertJsonPath('message', "Access denied. You may not view this student's data.");
    $this->getJson("/api/v1/parent/child/{$otherChild->uuid}/summary")
        ->assertForbidden()
        ->assertJsonPath('message', 'Not allowed. This student is not registered as your child.');
});
