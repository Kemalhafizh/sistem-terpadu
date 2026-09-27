<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

/**
 * GET /api/v1/teacher/cbt/exams — the exams a teacher can open in the proctor
 * screen. Must follow the same ownership rule as the token endpoint
 * (CbtAccessControlService::canViewToken): UTS/UAS → operator_id,
 * ulangan harian/try out → created_by.
 */
beforeEach(function () {
    $this->subject = Subject::factory()->create(['name' => 'Matematika']);
    $this->classroom = Classroom::factory()->create(['name' => 'XI IPA 1']);
    $this->academicYear = AcademicYear::factory()->create();

    $this->creator = User::factory()->create(['role' => 'guru', 'name' => 'Guru Mapel']);
    $this->operator = User::factory()->create(['role' => 'guru', 'name' => 'Guru Operator']);
    $this->otherTeacher = User::factory()->create(['role' => 'guru', 'name' => 'Guru Lain']);

    $this->makeExam = function (array $overrides = []) {
        return Exam::create(array_merge([
            'title' => 'Ujian',
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'created_by' => $this->creator->id,
            'operator_id' => null,
            'type' => 'ulangan_harian',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'total_questions' => 10,
            'status' => 'published',
            'mobile_only' => true,
        ], $overrides));
    };
});

test('operator melihat UTS yang dia operasikan, bukan UTS buatannya sendiri yang dioperasikan guru lain', function () {
    $uts = ($this->makeExam)([
        'title' => 'UTS Matematika',
        'type' => 'uts',
        'created_by' => $this->creator->id,
        'operator_id' => $this->operator->id,
    ]);

    Sanctum::actingAs($this->operator);
    $this->getJson('/api/v1/teacher/cbt/exams')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $uts->id)
        ->assertJsonPath('data.0.title', 'UTS Matematika')
        ->assertJsonPath('data.0.subject', 'Matematika')
        ->assertJsonPath('data.0.classroom', 'XI IPA 1')
        ->assertJsonPath('data.0.is_ongoing', true);

    // The creator of a UTS is NOT its token holder, so it must not appear for them.
    Sanctum::actingAs($this->creator);
    $this->getJson('/api/v1/teacher/cbt/exams')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('pembuat ulangan harian melihat ujiannya; guru lain tidak', function () {
    $uh = ($this->makeExam)(['title' => 'PHB 1']);

    Sanctum::actingAs($this->creator);
    $this->getJson('/api/v1/teacher/cbt/exams')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $uh->id);

    Sanctum::actingAs($this->otherTeacher);
    $this->getJson('/api/v1/teacher/cbt/exams')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('draf dan ujian yang sudah berakhir tidak ditampilkan; ujian mendatang ditampilkan tidak ongoing', function () {
    ($this->makeExam)(['title' => 'Draf', 'status' => 'draft']);
    ($this->makeExam)([
        'title' => 'Sudah Lewat',
        'start_time' => now()->subHours(3),
        'end_time' => now()->subHour(),
    ]);
    $upcoming = ($this->makeExam)([
        'title' => 'Besok',
        'start_time' => now()->addDay(),
        'end_time' => now()->addDay()->addHours(2),
    ]);

    Sanctum::actingAs($this->creator);
    $this->getJson('/api/v1/teacher/cbt/exams')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $upcoming->id)
        ->assertJsonPath('data.0.is_ongoing', false);
});

test('setiap ujian di daftar benar-benar bisa dibuka tokennya oleh guru tersebut', function () {
    ($this->makeExam)(['title' => 'PHB 1']);
    ($this->makeExam)(['title' => 'UAS', 'type' => 'uas', 'operator_id' => $this->creator->id]);
    // Not theirs: UTS operated by someone else.
    ($this->makeExam)(['title' => 'UTS Lain', 'type' => 'uts', 'operator_id' => $this->operator->id]);

    Sanctum::actingAs($this->creator);
    $ids = collect($this->getJson('/api/v1/teacher/cbt/exams')->assertOk()->json('data'))->pluck('id');

    expect($ids)->toHaveCount(2);
    foreach ($ids as $id) {
        $this->getJson("/api/v1/teacher/cbt/exams/{$id}/token")->assertOk();
    }
});

test('siswa tidak dapat mengakses daftar ujian pengawas', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'student']));

    $this->getJson('/api/v1/teacher/cbt/exams')->assertForbidden();
});
