<?php

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
 * the school has one calendar. The app (GET academic-calendar) used
 * to read a second "enterprise" calendar that nobody filled from the web, so
 * events entered on the web calendar never reached phones.
 */

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->year = AcademicYear::factory()->create(['is_active' => true]);
    $this->creator = User::factory()->create(['role' => 'admin']);
});

function schoolEvent(object $test, array $attributes = []): AcademicEvent
{
    return AcademicEvent::create([
        'title' => 'Ujian Tengah Semester', 'event_type' => 'exam', 'start_date' => '2026-10-20 07:00:00',
        'end_date' => '2026-10-24 12:00:00', 'academic_year_id' => $test->year->id, 'created_by' => $test->creator->id,
        ...$attributes,
    ]);
}

function calendarTitles(object $test, User $user, string $query = ''): array
{
    return collect($test->actingAs($user)->getJson('/api/v1/academic-calendar'.$query)->assertOk()->json('data'))
        ->pluck('title')->sort()->values()->all();
}

test('aplikasi membaca kalender yang sama dengan web', function () {
    schoolEvent($this, ['title' => 'Libur Maulid', 'event_type' => 'holiday', 'start_date' => '2026-10-15 00:00:00', 'end_date' => null]);

    $this->actingAs($this->user)->getJson('/api/v1/academic-calendar')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.title', 'Libur Maulid')
        ->assertJsonPath('data.0.category', 'holiday')
        ->assertJsonPath('data.0.is_holiday', true)
        ->assertJsonPath('data.0.start_time', '2026-10-15 00:00:00')
        ->assertJsonPath('data.0.end_time', null)
        // Events have no location column; the API says so instead of inventing one.
        ->assertJsonPath('data.0.location', null);
});

test('kalender kosong tetap kosong', function () {
    $this->actingAs($this->user)->getJson('/api/v1/academic-calendar')->assertOk()->assertJsonPath('data', []);
});

test('event untuk peran lain dan event nonaktif tidak terlihat', function () {
    schoolEvent($this, ['title' => 'Untuk semua']);
    schoolEvent($this, ['title' => 'Untuk siswa', 'target_role' => 'student']);
    schoolEvent($this, ['title' => 'Rapat guru', 'target_role' => 'guru']);
    schoolEvent($this, ['title' => 'Dibatalkan', 'is_active' => false]);

    // The form stores `student`; an account spelled `siswa` is the same audience.
    $siswa = User::factory()->create(['role' => 'siswa']);

    expect(calendarTitles($this, $siswa))->toBe(['Untuk semua', 'Untuk siswa'])
        ->and(calendarTitles($this, User::factory()->create(['role' => 'guru'])))->toBe(['Rapat guru', 'Untuk semua']);
});

test('event per kelas hanya untuk siswa kelas itu dan orang tuanya, semua anaknya', function () {
    [$a, $b, $c] = Classroom::factory()->count(3)->create();
    schoolEvent($this, ['title' => 'Kelas A', 'target_classroom_id' => $a->id]);
    schoolEvent($this, ['title' => 'Kelas B', 'target_classroom_id' => $b->id]);
    schoolEvent($this, ['title' => 'Kelas C', 'target_classroom_id' => $c->id]);

    $student = Student::factory()->create(['classroom_id' => $a->id]);
    $parent = User::factory()->create(['role' => 'orang_tua']);
    Student::factory()->create(['classroom_id' => $a->id, 'parent_id' => $parent->id]);
    Student::factory()->create(['classroom_id' => $b->id, 'parent_id' => $parent->id]);

    expect(calendarTitles($this, $student->user))->toBe(['Kelas A'])
        // Used to be the first child's class only.
        ->and(calendarTitles($this, $parent))->toBe(['Kelas A', 'Kelas B']);
});

test('filter bulan, tahun, dan kategori', function () {
    schoolEvent($this, ['title' => 'Oktober']);
    schoolEvent($this, ['title' => 'November', 'start_date' => '2026-11-02 07:00:00', 'event_type' => 'meeting']);

    expect(calendarTitles($this, $this->user, '?month=11&year=2026'))->toBe(['November'])
        ->and(calendarTitles($this, $this->user, '?category=exam'))->toBe(['Oktober']);
    $this->actingAs($this->user)->getJson('/api/v1/academic-calendar?month=13')->assertUnprocessable();
});

test('user can export ical calendar feed', function () {
    schoolEvent($this, ['title' => 'Upacara', 'start_date' => '2026-10-28 07:00:00', 'end_date' => null]);
    schoolEvent($this, ['title' => 'Rapat guru', 'target_role' => 'guru']);

    $ics = $this->actingAs($this->user)->get('/api/v1/academic-calendar/export-ical?year=2026')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
        ->getContent();

    expect($ics)->toContain('SUMMARY:Upacara')->not->toContain('Rapat guru');
    // No end date: the event ends when it starts, not two made-up hours later.
    preg_match('/DTSTART:(\S+)\r\nDTEND:(\S+)/', $ics, $m);
    expect($m[1])->toBe($m[2]);
});

test('kalender kedua (calendar-hub) sudah tidak ada', function () {
    expect(Route::has('calendar.events_json'))->toBeFalse()
        ->and(Route::has('calendar.rsvp'))->toBeFalse()
        ->and(route('calendar.index', absolute: false))->toBe('/calendar');
});
