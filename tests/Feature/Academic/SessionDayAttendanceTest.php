<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\Schedule;
use App\Models\SchedulePreset;
use App\Models\ScheduleTime;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/*
 * FASE 78.2 (Android) — presensi QR per mapel satu hari untuk siswa dan
 * orang tua, sumber widget presensi. Fixture: Senin 2026-10-05, jam 1–2 =
 * 07:00–08:30, scan setelah 07:15 tercatat telat.
 */

beforeEach(function () {
    Cache::forget('academic_year_current');
    $this->day = '2026-10-05';
    $this->travelTo(Carbon::parse($this->day.' 07:05:00'));

    $this->year = AcademicYear::factory()->create(['is_active' => true]);
    $preset = SchedulePreset::create(['name' => 'Reguler', 'is_active' => true]);
    foreach ([[1, '07:00', '07:45'], [2, '07:45', '08:30'], [3, '08:30', '09:15']] as [$n, $start, $end]) {
        ScheduleTime::create(['schedule_preset_id' => $preset->id, 'session_number' => $n, 'start_time' => $start, 'end_time' => $end]);
    }

    $this->teacher = User::factory()->create(['role' => 'guru', 'name' => 'Pak Hadi']);
    $this->classroom = Classroom::factory()->create(['name' => 'X-1 (IPA)']);
    $this->parent = User::factory()->create(['role' => 'orang_tua']);
    $this->student = Student::factory()->create([
        'classroom_id' => $this->classroom->id,
        'parent_id' => $this->parent->id,
        'user_id' => User::factory()->create(['role' => 'siswa', 'name' => 'Citra'])->id,
    ]);

    $this->schedule = Schedule::factory()->create([
        'academic_year_id' => $this->year->id,
        'classroom_id' => $this->classroom->id,
        'subject_id' => Subject::factory()->create(['name' => 'Matematika'])->id,
        'teacher_id' => $this->teacher->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    $this->start = function () {
        return $this->actingAs($this->teacher)
            ->postJson("/api/v1/teacher/class-sessions/{$this->schedule->id}/start")
            ->assertCreated()
            ->json('data.id');
    };
    $this->scan = function (int $sessionId) {
        $token = $this->actingAs($this->teacher)->getJson("/api/v1/teacher/class-sessions/{$sessionId}/qr")->json('data.qr_token');

        return $this->actingAs($this->student->user)
            ->postJson('/api/v1/student/class-session/scan-qr', ['qr_token' => $token])
            ->assertOk();
    };
    $this->mine = fn (string $query = '') => $this->actingAs($this->student->user)
        ->getJson('/api/v1/student/class-session/attendance'.$query);
});

test('belum ada sesi: daftar kosong dan ringkasan nol, bukan angka karangan', function () {
    ($this->mine)()
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.date', $this->day)
        ->assertJsonCount(0, 'data.sessions')
        ->assertJsonPath('data.summary.sessions', 0)
        ->assertJsonPath('data.summary.recorded', 0)
        ->assertJsonPath('data.summary.hadir', 0);
});

test('kelas berjalan dan belum scan: alpha otomatis ditandai menunggu scan, belum dihitung', function () {
    $id = ($this->start)();

    ($this->mine)()
        ->assertOk()
        ->assertJsonCount(1, 'data.sessions')
        ->assertJsonPath('data.sessions.0.session_id', $id)
        ->assertJsonPath('data.sessions.0.subject_name', 'Matematika')
        ->assertJsonPath('data.sessions.0.teacher_name', 'Pak Hadi')
        ->assertJsonPath('data.sessions.0.scheduled_start', '07:00')
        ->assertJsonPath('data.sessions.0.scheduled_end', '08:30')
        ->assertJsonPath('data.sessions.0.session_status', 'active')
        ->assertJsonPath('data.sessions.0.status_code', 'alpha')
        ->assertJsonPath('data.sessions.0.check_in_method', 'auto_alpha')
        ->assertJsonPath('data.sessions.0.checked_in_at', null)
        ->assertJsonPath('data.sessions.0.awaiting_scan', true)
        ->assertJsonPath('data.summary.sessions', 1)
        ->assertJsonPath('data.summary.recorded', 0)
        ->assertJsonPath('data.summary.alpha', 0);
});

test('setelah scan QR: hadir dengan jam ISO-8601 dan label dalam bahasa pengguna', function () {
    $id = ($this->start)();
    ($this->scan)($id);

    $this->student->user->update(['preferred_locale' => 'en']);

    ($this->mine)()
        ->assertOk()
        ->assertJsonPath('data.sessions.0.status_code', 'hadir')
        ->assertJsonPath('data.sessions.0.status', 'Present')
        ->assertJsonPath('data.sessions.0.check_in_method', 'qr_scan')
        ->assertJsonPath('data.sessions.0.checked_in_at', Carbon::parse($this->day.' 07:05:00')->toIso8601String())
        ->assertJsonPath('data.sessions.0.awaiting_scan', false)
        ->assertJsonPath('data.summary.recorded', 1)
        ->assertJsonPath('data.summary.hadir', 1);
});

test('kelas yang selesai tanpa scan dihitung alpha', function () {
    $id = ($this->start)();
    ClassSession::whereKey($id)->update(['status' => ClassSession::STATUS_COMPLETED]);

    ($this->mine)()
        ->assertOk()
        ->assertJsonPath('data.sessions.0.status_code', 'alpha')
        ->assertJsonPath('data.sessions.0.awaiting_scan', false)
        ->assertJsonPath('data.summary.recorded', 1)
        ->assertJsonPath('data.summary.alpha', 1);
});

test('sesi kelas lain dan sesi yang dibatalkan tidak ikut', function () {
    $id = ($this->start)();
    $other = ClassSession::whereKey($id)->first()->replicate(['uuid']);
    $other->fill(['classroom_id' => Classroom::factory()->create()->id, 'schedule_id' => null])->save();
    $cancelled = ClassSession::whereKey($id)->first()->replicate(['uuid']);
    $cancelled->fill(['status' => ClassSession::STATUS_CANCELLED, 'schedule_id' => null])->save();

    ($this->mine)()
        ->assertOk()
        ->assertJsonCount(1, 'data.sessions')
        ->assertJsonPath('data.sessions.0.session_id', $id);
});

test('tanggal lain dibaca dari tanggal itu, tanggal masa depan dan format salah ditolak', function () {
    ($this->start)();

    ($this->mine)('?date=2026-10-02')
        ->assertOk()
        ->assertJsonPath('data.date', '2026-10-02')
        ->assertJsonCount(0, 'data.sessions');

    ($this->mine)('?date=2026-10-06')
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Tanggal presensi tidak boleh di masa depan.');

    ($this->mine)('?date=05-10-2026')->assertStatus(422)->assertJsonPath('success', false);
});

test('orang tua melihat presensi per mapel anaknya sendiri', function () {
    $id = ($this->start)();
    ($this->scan)($id);

    $this->actingAs($this->parent)
        ->getJson("/api/v1/parent/child/{$this->student->uuid}/class-session-attendance")
        ->assertOk()
        ->assertJsonPath('data.date', $this->day)
        ->assertJsonPath('data.sessions.0.session_id', $id)
        ->assertJsonPath('data.sessions.0.status_code', 'hadir')
        ->assertJsonPath('data.summary.hadir', 1);
});

test('orang tua lain ditolak, anak yang tidak ada 404, dan peran lain tidak bisa masuk', function () {
    $otherParent = User::factory()->create(['role' => 'orang_tua']);

    $this->actingAs($otherParent)
        ->getJson("/api/v1/parent/child/{$this->student->uuid}/class-session-attendance")
        ->assertForbidden();

    $this->actingAs($this->parent)
        ->getJson('/api/v1/parent/child/00000000-0000-0000-0000-000000000000/class-session-attendance')
        ->assertNotFound();

    // A student cannot read a classmate through the parent route, and a parent has no student route.
    $this->actingAs($this->student->user)
        ->getJson("/api/v1/parent/child/{$this->student->uuid}/class-session-attendance")
        ->assertForbidden();
    $this->actingAs($this->parent)
        ->getJson('/api/v1/student/class-session/attendance')
        ->assertForbidden();
});

test('orang tua juga ditolak untuk tanggal masa depan', function () {
    $this->actingAs($this->parent)
        ->getJson("/api/v1/parent/child/{$this->student->uuid}/class-session-attendance?date=2026-10-06")
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('siswa yang pindah kelas tetap melihat sesi yang ia ikuti hari itu', function () {
    $id = ($this->start)();
    ($this->scan)($id);
    $this->student->update(['classroom_id' => Classroom::factory()->create()->id]);

    ($this->mine)()
        ->assertOk()
        ->assertJsonCount(1, 'data.sessions')
        ->assertJsonPath('data.sessions.0.session_id', $id)
        ->assertJsonPath('data.sessions.0.status_code', 'hadir');
});

test('setiap entri sesi membawa kunci yang dibaca aplikasi', function () {
    ($this->start)();

    ($this->mine)()->assertJsonStructure(['data' => [
        'date',
        'sessions' => [['session_id', 'subject_name', 'teacher_name', 'jam_ke', 'scheduled_start', 'scheduled_end',
            'session_status', 'status_code', 'status', 'check_in_method', 'checked_in_at', 'awaiting_scan']],
        'summary' => ['sessions', 'recorded', 'hadir', 'telat', 'sakit', 'izin', 'alpha'],
    ]]);
    expect(SessionAttendance::count())->toBe(1);
});
