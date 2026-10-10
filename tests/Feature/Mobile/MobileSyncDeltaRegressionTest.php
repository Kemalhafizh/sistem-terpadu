<?php

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\User;

/*
 * Regression: GET /api/v1/mobile/sync/pull used to
 *  - crash for any user with a Student record (App\Models\TahfidzRecord never existed),
 *  - crash for parents (queried students.parent_user_id; the column is parent_id),
 *  - silently omit announcements and calendar (class_exists() on unimported names),
 *  - ignore the "siswa" / "orang_tua" role names.
 * The existing gateway tests used a student user without a Student row, so the
 * student branch never ran.
 */

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'guru']);
    $this->parent = User::factory()->create(['role' => 'parent']);
    $this->student = Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student', 'name' => 'Aisyah'])->id,
        'parent_id' => $this->parent->id,
    ]);

    TahfidzLog::create([
        'student_id' => $this->student->id,
        'teacher_id' => $this->teacher->id,
        'surah_number' => 67,
        'surah_name' => 'Al-Mulk',
        'from_ayat' => 1,
        'to_ayat' => 10,
        'type' => 'ziyadah',
        'grade' => 'A',
        'date' => now()->toDateString(),
    ]);

    Announcement::create(['title' => 'Untuk semua', 'content' => '<p>Libur <b>nasional</b></p>', 'created_by' => $this->teacher->id, 'is_active' => true]);
    Announcement::create(['title' => 'Khusus guru', 'content' => 'Rapat', 'target_role' => 'guru', 'created_by' => $this->teacher->id, 'is_active' => true]);
    Announcement::create(['title' => 'Nonaktif', 'content' => 'Draft', 'created_by' => $this->teacher->id, 'is_active' => false]);

    AcademicEvent::create([
        'title' => 'Libur Maulid',
        'event_type' => 'holiday',
        'start_date' => now()->addDays(3),
        'academic_year_id' => AcademicYear::factory()->create()->id,
        'created_by' => $this->teacher->id,
        'is_active' => true,
    ]);

    $this->pull = fn (User $user) => $this->actingAs($user)
        ->getJson('/api/v1/mobile/sync/pull?since='.urlencode(now()->subDays(7)->toIso8601String()));
});

test('siswa dengan data Student menerima delta tahfidz, pengumuman, dan kalender', function () {
    $response = ($this->pull)($this->student->user)->assertOk();

    $response->assertJsonPath('delta.student_data.tahfidz.0.surah_name', 'Al-Mulk')
        ->assertJsonPath('delta.student_data.tahfidz.0.surah_number', 67)
        ->assertJsonPath('delta.student_data.tahfidz.0.from_ayat', 1)
        ->assertJsonPath('delta.student_data.tahfidz.0.to_ayat', 10)
        ->assertJsonPath('delta.student_data.tahfidz.0.grade', 'A')
        ->assertJsonPath('delta.student_data.tahfidz.0.date', now()->toDateString())
        ->assertJsonPath('delta.calendar.0.title', 'Libur Maulid')
        ->assertJsonPath('delta.calendar.0.is_holiday', true)
        ->assertJsonPath('delta.calendar.0.event_date', now()->addDays(3)->toDateString());

    // Only active announcements addressed to everyone or to this role.
    expect(array_column($response->json('delta.announcements'), 'title'))->toBe(['Untuk semua'])
        ->and($response->json('delta.announcements.0.content'))->toBe('Libur nasional')
        ->and($response->json('records_synced'))->toBe(3);
});

test('peran siswa juga dilayani', function () {
    $this->student->user->update(['role' => 'siswa']);

    ($this->pull)($this->student->user)->assertOk()
        ->assertJsonPath('delta.student_data.tahfidz.0.surah_name', 'Al-Mulk');
});

test('orang tua menerima delta setiap anak lewat kolom parent_id', function (string $role) {
    $this->parent->update(['role' => $role]);

    ($this->pull)($this->parent)->assertOk()
        ->assertJsonPath('delta.children_data.0.student_id', $this->student->id)
        ->assertJsonPath('delta.children_data.0.student_name', 'Aisyah')
        ->assertJsonPath('delta.children_data.0.delta.tahfidz.0.surah_name', 'Al-Mulk');
})->with(['parent', 'orang_tua']);
