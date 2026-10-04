<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

/*
 * /api/v1/teaching-journals used to list two hardcoded sample journals when a
 * teacher had none, and its POST answered "berhasil disimpan & disahkan" with
 * a random id without saving anything. The Android app never called it; it
 * files journals through /api/v1/teacher/journals, which does save. The stub
 * routes are gone.
 */

beforeEach(function () {
    Cache::forget('academic_year_current');
    $this->year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->journal = [
        'subject_id' => Subject::factory()->create()->id,
        'classroom_id' => Classroom::factory()->create()->id,
        'teaching_date' => '2026-09-06',
        'jam_ke' => 3,
        'topic' => 'Kajian Relativitas Khusus',
        'learning_activity' => 'Diskusi dilatasi waktu.',
        'learning_method' => 'Diskusi kelompok',
        'students_present' => 35,
        'students_absent' => 1,
    ];
    // The teacher teaches this subject in this class this year.
    Schedule::create([
        'academic_year_id' => $this->year->id, 'classroom_id' => $this->journal['classroom_id'],
        'subject_id' => $this->journal['subject_id'], 'teacher_id' => $this->guru->id,
        'day' => 'Senin', 'session_start' => 3, 'session_end' => 4,
    ]);
});

test('rute jurnal tiruan sudah tidak ada', function () {
    Sanctum::actingAs($this->guru);

    $this->getJson('/api/v1/teaching-journals/my')->assertNotFound();
    $this->postJson('/api/v1/teaching-journals', ['topic' => 'Apa saja'])->assertNotFound();

    expect(TeachingJournal::count())->toBe(0);
});

test('guru menyimpan jurnal lewat teacher/journals dan barisnya benar-benar tercipta', function () {
    Sanctum::actingAs($this->guru);

    $response = $this->postJson('/api/v1/teacher/journals', $this->journal)
        ->assertCreated()
        ->assertJsonPath('success', true);

    $saved = TeachingJournal::sole();
    expect($saved->user_id)->toBe($this->guru->id)
        ->and($saved->topic)->toBe('Kajian Relativitas Khusus')
        ->and($saved->academic_year_id)->toBe($this->year->id)
        ->and($saved->status)->toBe('draft')
        ->and($response->json('data.uuid'))->toBe($saved->uuid);

    // The list reads the same row back, no samples.
    $this->getJson('/api/v1/teacher/journals')->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.topic', 'Kajian Relativitas Khusus');
});

test('daftar jurnal guru tanpa jurnal kosong, bukan contoh', function () {
    Sanctum::actingAs($this->guru);

    $this->getJson('/api/v1/teacher/journals')->assertOk()->assertJsonCount(0, 'data');
});

test('guru hanya bisa mengisi jurnal untuk pelajaran yang ia ajar tahun ini', function (string $case) {
    $teacher = $this->guru;
    $payload = $this->journal;

    match ($case) {
        'kelas lain' => $payload['classroom_id'] = Classroom::factory()->create()->id,
        'mapel lain' => $payload['subject_id'] = Subject::factory()->create()->id,
        'guru lain' => $teacher = User::factory()->create(['role' => 'guru']),
        'tahun ajaran lalu' => (function () {
            $this->year->update(['is_active' => false]);
            Cache::forget('academic_year_current');
            AcademicYear::create(['year' => '2027/2028', 'semester' => 'Ganjil', 'is_active' => true]);
        })(),
    };

    Sanctum::actingAs($teacher);
    $this->postJson('/api/v1/teacher/journals', $payload)
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Anda tidak mengajar mata pelajaran ini di kelas tersebut pada tahun ajaran aktif.');

    expect(TeachingJournal::count())->toBe(0);
})->with(['kelas lain', 'mapel lain', 'guru lain', 'tahun ajaran lalu']);
