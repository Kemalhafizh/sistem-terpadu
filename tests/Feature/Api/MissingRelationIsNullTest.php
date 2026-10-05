<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

// A schedule or journal whose subject, class or teacher is gone used to come back
// as "N/A", a name the app showed as if it were real. Missing is null; the app
// renders "–".

beforeEach(function () {
    $this->year = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create(['name' => 'XI MIPA 2']);
    $this->subject = Subject::factory()->create(['name' => 'Fisika', 'code' => 'FIS']);
    $this->teacher = User::factory()->create(['role' => 'guru', 'name' => 'Bu Rina']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
    Student::factory()->create(['user_id' => $this->studentUser->id, 'classroom_id' => $this->classroom->id]);

    $this->schedule = Schedule::create([
        'academic_year_id' => $this->year->id,
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'day' => 'Senin',
        'session_start' => '07:00',
        'session_end' => '08:30',
    ]);
});

/**
 * Rows left pointing at a subject that no longer exists, as on servers without
 * the foreign keys. SQLite ignores `foreign_keys = OFF` inside the test
 * transaction, so the check is deferred to a commit that never comes.
 */
function deferForeignKeyChecks(): void
{
    DB::statement('PRAGMA defer_foreign_keys = ON');
}

test('the student schedule keeps a lesson whose subject was deleted, with subject_name null', function () {
    deferForeignKeyChecks();
    $this->subject->delete();
    Sanctum::actingAs($this->studentUser);

    $this->getJson('/api/v1/student/schedule')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.subject_name', null)
        ->assertJsonPath('data.0.subject_code', null)
        ->assertJsonPath('data.0.teacher_name', 'Bu Rina')
        ->assertJsonPath('data.0.classroom_name', 'XI MIPA 2');
});

test('the teacher schedule sends null for a deleted subject too', function () {
    deferForeignKeyChecks();
    $this->subject->delete();
    Sanctum::actingAs($this->teacher);

    $this->getJson('/api/v1/teacher/schedule')
        ->assertOk()
        ->assertJsonPath('data.0.subject_name', null)
        ->assertJsonPath('data.0.subject_code', null)
        ->assertJsonPath('data.0.classroom_name', 'XI MIPA 2');
});

test('a removed teacher is null in the student schedule, not N/A', function () {
    $this->teacher->delete(); // soft delete: the relation no longer resolves
    Sanctum::actingAs($this->studentUser);

    $this->getJson('/api/v1/student/schedule')
        ->assertOk()
        ->assertJsonPath('data.0.teacher_name', null)
        ->assertJsonPath('data.0.subject_name', 'Fisika');
});

test('a journal whose subject is gone sends null and builds the attachment url from the public disk', function () {
    config(['filesystems.disks.public.url' => 'https://cdn.sekolah.test/storage']);
    $journal = TeachingJournal::create([
        'user_id' => $this->teacher->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->year->id,
        'teaching_date' => '2026-09-21',
        'jam_ke' => 1,
        'topic' => 'Hukum Newton',
        'learning_activity' => 'Diskusi',
        'learning_method' => 'Ceramah',
        'students_present' => 30,
        'students_absent' => 2,
        'attachment_path' => 'journals/foto-kelas.jpg',
        'status' => 'submitted',
    ]);
    // teaching_journals.subject_id cascades, so the journal points at a subject id that is gone.
    deferForeignKeyChecks();
    $journal->forceFill(['subject_id' => 999_999])->saveQuietly();
    Sanctum::actingAs($this->teacher);

    $this->getJson('/api/v1/teacher/journals')
        ->assertOk()
        ->assertJsonPath('data.0.subject_name', null)
        ->assertJsonPath('data.0.classroom_name', 'XI MIPA 2')
        ->assertJsonPath('data.0.attachment_url', 'https://cdn.sekolah.test/storage/journals/foto-kelas.jpg');
});
