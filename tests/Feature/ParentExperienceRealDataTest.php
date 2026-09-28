<?php

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

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-09-30 10:00:00')); // a Wednesday
    $this->year = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->subject = Subject::factory()->create(['name' => 'Fisika']);
    $this->parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    $this->teacher = User::factory()->create(['role' => 'guru']);
    $this->child = Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student'])->id,
        'parent_id' => $this->parent->id,
        'classroom_id' => $this->classroom->id,
    ]);
    $this->classmate = Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student'])->id,
        'classroom_id' => $this->classroom->id,
    ]);
    $this->attend = fn (Student $s, string $date, string $status) => Attendance::create([
        'student_id' => $s->id, 'academic_year_id' => $this->year->id, 'classroom_id' => $this->classroom->id,
        'date' => $date, 'status' => $status, 'recorded_by' => $this->teacher->id,
    ]);
    $this->grade = fn (Student $s, string $type, float $score) => Grade::create([
        'student_id' => $s->id, 'subject_id' => $this->subject->id, 'academic_year_id' => $this->year->id,
        'type' => $type, 'score' => $score, 'recorded_by' => $this->teacher->id,
    ]);
});

afterEach(fn () => Carbon::setTestNow());

test('a child with no records gets an empty feed and no invented numbers', function () {
    Sanctum::actingAs($this->parent);

    $this->getJson('/api/v1/parent/feed')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/parent/weekly-digest')->assertOk()
        ->assertJsonPath('data.attendancePercentage', null)
        ->assertJsonPath('data.averageGrade', null)
        ->assertJsonPath('data.ibadahScore', null)
        ->assertJsonPath('data.highlights', []);
    $this->getJson('/api/v1/parent/comparison')->assertOk()->assertJsonCount(0, 'data');
});

test('the feed shows this child\'s own records, newest first, and nobody else\'s', function () {
    ($this->attend)($this->child, '2026-09-29', 'H');
    ($this->attend)($this->child, '2026-09-30', 'S');
    ($this->attend)($this->classmate, '2026-09-30', 'A');
    ($this->grade)($this->child, 'uh', 88);
    StudentPointLog::create([
        'student_id' => $this->child->id, 'type' => 'reward', 'points' => 10,
        'description' => 'Juara lomba', 'created_by' => $this->teacher->id,
    ]);

    Sanctum::actingAs($this->parent);
    $feed = collect($this->getJson('/api/v1/parent/feed')->assertOk()->json('data'));

    expect($feed)->toHaveCount(4)
        ->and($feed->pluck('title'))->toContain('Presensi: Sakit', 'Presensi: Hadir', 'Nilai UH Fisika', 'Poin prestasi +10')
        ->and($feed->pluck('title'))->not->toContain('Presensi: Tanpa keterangan')
        ->and($feed->pluck('timestamp')->all())->toBe($feed->pluck('timestamp')->sortDesc()->values()->all());
});

test('the weekly digest is computed from this week only', function () {
    ($this->attend)($this->child, '2026-09-22', 'A'); // last week
    ($this->attend)($this->child, '2026-09-28', 'H');
    ($this->attend)($this->child, '2026-09-29', 'H');
    ($this->attend)($this->child, '2026-09-30', 'I');
    ($this->grade)($this->child, 'uh', 80);
    ($this->grade)($this->child, 'tugas', 91);

    Sanctum::actingAs($this->parent);
    $digest = $this->getJson('/api/v1/parent/weekly-digest')->assertOk()->json('data');

    expect($digest['attendancePercentage'])->toEqual(66.7)
        ->and($digest['averageGrade'])->toEqual(85.5)
        ->and($digest['ibadahScore'])->toBeNull()
        ->and($digest['highlights'])->toContain('Hadir 2 dari 3 presensi tercatat.', 'Tidak hadir: 1 izin.', '2 nilai baru masuk.');
});

test('the class comparison uses real classmates\' grades', function () {
    ($this->grade)($this->child, 'uh', 90);
    ($this->grade)($this->child, 'tugas', 80);
    ($this->grade)($this->classmate, 'uh', 60);

    Sanctum::actingAs($this->parent);
    $rows = $this->getJson('/api/v1/parent/comparison')->assertOk()->json('data');

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['subject'])->toBe('Fisika')
        ->and($rows[0]['childScore'])->toEqual(85)
        ->and($rows[0]['classAverage'])->toEqual(76.7);
});

test('the bill shortcut appears only when a bill is actually open', function () {
    Sanctum::actingAs($this->parent);

    $ids = collect($this->getJson('/api/v1/parent/actions')->assertOk()->json('data'))->pluck('id');
    expect($ids)->not->toContain('pay_spp');
});
