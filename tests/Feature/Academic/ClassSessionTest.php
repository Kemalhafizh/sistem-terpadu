<?php

use App\Jobs\SendFcmNotificationJob;
use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\Schedule;
use App\Models\SchedulePreset;
use App\Models\ScheduleTime;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

/*
 * FASE 117 — Sesi Kelas Hidup & Presensi Per-Mapel.
 *
 * Fixture: Monday 2026-10-05. Preset periods 1 = 07:00–07:45, 2 = 07:45–08:30,
 * so the Monday schedule "jam 1–2" runs 07:00–08:30: startable from 06:50,
 * scans after 07:15 are "telat", auto-closed after 08:35.
 */

const MONDAY = '2026-10-05';

beforeEach(function () {
    Cache::forget('academic_year_current');
    $this->travelTo(Carbon::parse(MONDAY.' 07:05:00'));

    $this->year = AcademicYear::factory()->create(['is_active' => true]);
    $preset = SchedulePreset::create(['name' => 'Reguler', 'is_active' => true]);
    foreach ([[1, '07:00', '07:45'], [2, '07:45', '08:30'], [3, '08:30', '09:15']] as [$n, $start, $end]) {
        ScheduleTime::create(['schedule_preset_id' => $preset->id, 'session_number' => $n, 'start_time' => $start, 'end_time' => $end]);
    }

    $this->teacher = User::factory()->create(['role' => 'guru', 'name' => 'Pak Hadi']);
    $this->classroom = Classroom::factory()->create(['name' => 'X-1 (IPA)']);
    $this->subject = Subject::factory()->create(['name' => 'Matematika Peminatan']);

    $this->students = collect(['Citra', 'Andi', 'Budi'])->map(fn ($name) => Student::factory()->create([
        'classroom_id' => $this->classroom->id,
        'user_id' => User::factory()->create(['role' => 'student', 'name' => $name])->id,
    ]));
    $this->outsider = Student::factory()->create();

    $this->schedule = Schedule::factory()->create([
        'academic_year_id' => $this->year->id,
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    $this->startSession = function (array $body = []) {
        return $this->actingAs($this->teacher)
            ->postJson("/api/v1/teacher/class-sessions/{$this->schedule->id}/start", $body);
    };
    $this->qrToken = function (int $sessionId) {
        return $this->actingAs($this->teacher)
            ->getJson("/api/v1/teacher/class-sessions/{$sessionId}/qr")
            ->json('data.qr_token');
    };
    $this->scan = function (Student $student, string $token) {
        return $this->actingAs($student->user)
            ->postJson('/api/v1/student/class-session/scan-qr', ['qr_token' => $token]);
    };
});

// ── Daftar sesi hari ini ─────────────────────────────────────────────────

test('sesi hari ini dimaterialisasi dari jadwal dengan jam dari preset aktif', function () {
    $response = $this->actingAs($this->teacher)->getJson('/api/v1/teacher/class-sessions/today');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.schedule_id', $this->schedule->id)
        ->assertJsonPath('data.0.subject_name', 'Matematika Peminatan')
        ->assertJsonPath('data.0.classroom_name', 'X-1 (IPA)')
        ->assertJsonPath('data.0.teacher_name', 'Pak Hadi')
        ->assertJsonPath('data.0.session_date', MONDAY)
        ->assertJsonPath('data.0.jam_ke', 1)
        ->assertJsonPath('data.0.scheduled_start', '07:00')
        ->assertJsonPath('data.0.scheduled_end', '08:30')
        ->assertJsonPath('data.0.startable_from', '06:50')
        ->assertJsonPath('data.0.status', 'scheduled')
        ->assertJsonPath('data.0.can_start', true)
        ->assertJsonPath('data.0.total_students', 0);

    // Polling every 30 s must not create duplicates.
    $this->actingAs($this->teacher)->getJson('/api/v1/teacher/class-sessions/today')->assertOk();
    expect(ClassSession::count())->toBe(1);
});

test('jadwal hari lain tidak muncul di sesi hari ini', function () {
    $this->schedule->update(['day' => 'Selasa']);

    $this->actingAs($this->teacher)->getJson('/api/v1/teacher/class-sessions/today')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

// ── Mulai kelas (aturan 1–3, 6) ──────────────────────────────────────────

test('guru memulai kelas: sesi aktif, seluruh siswa auto-alpha, siswa dapat push', function () {
    Bus::fake([SendFcmNotificationJob::class]);

    ($this->startSession)(['topic' => 'Limit fungsi aljabar'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.topic', 'Limit fungsi aljabar')
        ->assertJsonPath('data.total_students', 3)
        ->assertJsonPath('data.present_count', 0)
        ->assertJsonPath('data.absent_count', 3)
        ->assertJsonPath('data.can_start', false);

    $session = ClassSession::sole();
    expect($session->actual_start->format('H:i'))->toBe('07:05')
        ->and($session->attendances()->where('status', 'alpha')->where('check_in_method', 'auto_alpha')->count())->toBe(3)
        ->and($session->attendances()->where('student_id', $this->outsider->id)->exists())->toBeFalse();

    Bus::assertDispatched(SendFcmNotificationJob::class, fn ($job) => count($job->userIds) === 3
        && str_contains($job->title, 'Matematika Peminatan'));
});

test('kelas tidak bisa dimulai lebih dari 10 menit sebelum jadwal', function () {
    $this->travelTo(Carbon::parse(MONDAY.' 06:45:00'));

    ($this->startSession)()
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Kelas baru bisa dimulai pukul 06:50.');

    $this->travelTo(Carbon::parse(MONDAY.' 06:50:00'));
    ($this->startSession)()->assertCreated();
});

test('kelas tidak bisa dimulai setelah jam selesai jadwal', function () {
    $this->travelTo(Carbon::parse(MONDAY.' 08:31:00'));

    ($this->startSession)()
        ->assertStatus(422)
        ->assertJsonPath('message', 'Waktu jadwal kelas ini sudah lewat.');
});

test('guru lain tidak bisa memulai kelas yang bukan jadwalnya', function () {
    $other = User::factory()->create(['role' => 'guru']);

    $this->actingAs($other)->postJson("/api/v1/teacher/class-sessions/{$this->schedule->id}/start")
        ->assertForbidden()
        ->assertJsonPath('message', 'Anda tidak dijadwalkan mengajar pada jadwal ini.');
    expect(ClassSession::where('status', 'active')->count())->toBe(0);
});

test('satu jadwal hanya bisa dimulai sekali per hari', function () {
    ($this->startSession)()->assertCreated();

    ($this->startSession)()
        ->assertStatus(409)
        ->assertJsonPath('message', 'Sesi kelas ini sudah dimulai.')
        ->assertJsonPath('data.session_id', ClassSession::sole()->id);

    expect(ClassSession::count())->toBe(1)
        ->and(SessionAttendance::count())->toBe(3);
});

test('jadwal hari lain dan jam pelajaran yang belum dikonfigurasi ditolak', function () {
    $this->schedule->update(['day' => 'Rabu']);
    ($this->startSession)()->assertStatus(422)->assertJsonPath('message', 'Jadwal ini untuk hari Rabu, bukan hari ini.');

    $this->schedule->update(['day' => 'Senin', 'session_end' => 9]);
    ($this->startSession)()->assertStatus(422)
        ->assertJsonPath('message', 'Jam pelajaran untuk jadwal ini belum dikonfigurasi. Hubungi Waka Kurikulum.');
});

test('siswa dan orang tua tidak bisa mengakses endpoint guru', function () {
    $this->actingAs($this->students[0]->user)->getJson('/api/v1/teacher/class-sessions/today')->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'parent']))
        ->postJson("/api/v1/teacher/class-sessions/{$this->schedule->id}/start")->assertForbidden();
});

// ── QR berputar (aturan 4) ───────────────────────────────────────────────

test('QR stabil dalam satu jendela 30 detik lalu berganti', function () {
    $id = ($this->startSession)()->json('data.id');

    $this->travelTo(Carbon::parse(MONDAY.' 07:10:01'));
    $first = $this->actingAs($this->teacher)->getJson("/api/v1/teacher/class-sessions/{$id}/qr")->assertOk();
    $first->assertJsonPath('data.session_uuid', ClassSession::sole()->uuid)
        ->assertJsonPath('data.rotation_seconds', 30)
        ->assertJsonPath('data.remaining_seconds', 29);
    expect($first->json('data.qr_token'))->toStartWith(ClassSession::sole()->uuid.'.');

    $this->travelTo(Carbon::parse(MONDAY.' 07:10:20'));
    expect(($this->qrToken)($id))->toBe($first->json('data.qr_token'));

    $this->travelTo(Carbon::parse(MONDAY.' 07:10:31'));
    expect(($this->qrToken)($id))->not->toBe($first->json('data.qr_token'));
});

test('QR hanya tersedia untuk sesi aktif milik guru', function () {
    $id = ($this->startSession)()->json('data.id');

    $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->getJson("/api/v1/teacher/class-sessions/{$id}/qr")->assertForbidden();

    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/end")->assertOk();
    $this->actingAs($this->teacher)->getJson("/api/v1/teacher/class-sessions/{$id}/qr")
        ->assertStatus(409)
        ->assertJsonPath('message', 'QR hanya tersedia saat sesi kelas aktif.');
});

// ── Scan QR siswa (aturan 5, 11) ─────────────────────────────────────────

test('siswa scan QR tepat waktu tercatat hadir', function () {
    $id = ($this->startSession)()->json('data.id');
    $student = $this->students[0];

    ($this->scan)($student, ($this->qrToken)($id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Presensi berhasil dicatat')
        ->assertJsonPath('data.status', 'hadir')
        ->assertJsonPath('data.session_subject', 'Matematika Peminatan')
        ->assertJsonPath('data.session_class', 'X-1 (IPA)');

    $row = SessionAttendance::where('student_id', $student->id)->sole();
    expect($row->status)->toBe('hadir')
        ->and($row->check_in_method)->toBe('qr_scan')
        ->and($row->recorded_by)->toBe($student->user_id)
        ->and($row->checked_in_at->format('H:i'))->toBe('07:05');
});

test('scan lebih dari 15 menit setelah jam mulai tercatat telat', function () {
    $id = ($this->startSession)()->json('data.id');
    $this->travelTo(Carbon::parse(MONDAY.' 07:16:00'));

    ($this->scan)($this->students[1], ($this->qrToken)($id))
        ->assertOk()
        ->assertJsonPath('data.status', 'telat')
        ->assertJsonPath('message', 'Presensi berhasil dicatat (terlambat)');

    $this->actingAs($this->teacher)->getJson("/api/v1/teacher/class-sessions/{$id}/students")
        ->assertJsonFragment(['student_name' => 'Andi', 'status' => 'telat']);
});

test('QR jendela sebelumnya masih diterima, yang lebih lama kedaluwarsa', function () {
    $id = ($this->startSession)()->json('data.id');
    $this->travelTo(Carbon::parse(MONDAY.' 07:10:05'));
    $token = ($this->qrToken)($id);

    // One rotation later: still accepted (scan in flight while the screen rotated).
    $this->travelTo(Carbon::parse(MONDAY.' 07:10:35'));
    ($this->scan)($this->students[0], $token)->assertOk();

    // Two rotations later: rejected with the exact message the app matches on.
    $this->travelTo(Carbon::parse(MONDAY.' 07:11:05'));
    ($this->scan)($this->students[1], $token)
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'QR token sudah kedaluwarsa');
});

test('token palsu dan QR asing ditolak', function () {
    $id = ($this->startSession)()->json('data.id');
    $uuid = ClassSession::find($id)->uuid;

    ($this->scan)($this->students[0], $uuid.'.'.str_repeat('0', 32))
        ->assertStatus(422)->assertJsonPath('message', 'QR token sudah kedaluwarsa');
    ($this->scan)($this->students[0], 'https://contoh.com/bukan-qr-sesi')
        ->assertStatus(422)->assertJsonPath('message', 'QR tidak valid. Pastikan Anda memindai QR sesi kelas dari layar guru.');
    ($this->scan)($this->students[0], '')->assertStatus(422)->assertJsonPath('success', false)->assertJsonStructure(['errors' => ['qr_token']]);

    expect(SessionAttendance::whereIn('status', ['hadir', 'telat'])->count())->toBe(0);
});

test('siswa kelas lain tidak bisa absen di sesi ini', function () {
    $id = ($this->startSession)()->json('data.id');

    ($this->scan)($this->outsider, ($this->qrToken)($id))
        ->assertForbidden()
        ->assertJsonPath('message', 'Anda tidak terdaftar di kelas ini');
    expect(SessionAttendance::where('student_id', $this->outsider->id)->exists())->toBeFalse();
});

test('scan kedua kali ditolak dan mengembalikan waktu check-in pertama', function () {
    $id = ($this->startSession)()->json('data.id');
    ($this->scan)($this->students[0], ($this->qrToken)($id))->assertOk();

    $this->travelTo(Carbon::parse(MONDAY.' 07:20:00'));
    ($this->scan)($this->students[0], ($this->qrToken)($id))
        ->assertStatus(409)
        ->assertJsonPath('message', 'Anda sudah tercatat hadir di sesi ini')
        ->assertJsonPath('data.status', 'hadir')
        ->assertJsonPath('data.checked_in_at', Carbon::parse(MONDAY.' 07:05:00')->toIso8601String());
});

test('tidak ada absensi retroaktif setelah sesi berakhir', function () {
    $id = ($this->startSession)()->json('data.id');
    $token = ($this->qrToken)($id);
    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/end")->assertOk();

    ($this->scan)($this->students[0], $token)
        ->assertStatus(409)
        ->assertJsonPath('message', 'Sesi kelas sudah berakhir.')
        ->assertJsonPath('error_code', 'session_ended');
});

test('siswa yang baru masuk kelas setelah sesi dimulai tetap bisa absen', function () {
    $id = ($this->startSession)()->json('data.id');
    $late = Student::factory()->create(['classroom_id' => $this->classroom->id]);

    ($this->scan)($late, ($this->qrToken)($id))->assertOk()->assertJsonPath('data.status', 'hadir');
    expect(ClassSession::find($id)->attendances()->count())->toBe(4);
});

test('banner sesi aktif siswa', function () {
    $student = $this->students[0];
    $this->actingAs($student->user)->getJson('/api/v1/student/class-session/active')
        ->assertOk()->assertJsonPath('data', null);

    $id = ($this->startSession)()->json('data.id');
    $this->actingAs($student->user)->getJson('/api/v1/student/class-session/active')
        ->assertOk()
        ->assertJsonPath('data.session_id', $id)
        ->assertJsonPath('data.subject_name', 'Matematika Peminatan')
        ->assertJsonPath('data.teacher_name', 'Pak Hadi')
        ->assertJsonPath('data.scheduled_end', '08:30')
        ->assertJsonPath('data.attendance_status', 'alpha')
        ->assertJsonPath('data.can_scan_qr', true);

    ($this->scan)($student, ($this->qrToken)($id))->assertOk();
    $this->actingAs($student->user)->getJson('/api/v1/student/class-session/active')
        ->assertJsonPath('data.can_scan_qr', false);

    $this->actingAs($this->outsider->user)->getJson('/api/v1/student/class-session/active')
        ->assertJsonPath('data', null);
});

// ── Absensi manual guru (aturan 7) ───────────────────────────────────────

test('daftar hadir diurutkan nama dengan status real-time', function () {
    $id = ($this->startSession)()->json('data.id');
    ($this->scan)($this->students[0], ($this->qrToken)($id))->assertOk();

    $response = $this->actingAs($this->teacher)->getJson("/api/v1/teacher/class-sessions/{$id}/students")->assertOk();

    expect(array_column($response->json('data'), 'student_name'))->toBe(['Andi', 'Budi', 'Citra']);
    $response->assertJsonPath('data.2.status', 'hadir')
        ->assertJsonPath('data.2.check_in_method', 'qr_scan')
        ->assertJsonPath('data.0.status', 'alpha')
        ->assertJsonPath('data.0.check_in_method', 'auto_alpha')
        ->assertJsonPath('data.0.student_nis', $this->students[1]->nis);
});

test('guru menandai siswa sakit secara manual', function () {
    $id = ($this->startSession)()->json('data.id');
    $student = $this->students[2];

    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/attendance/manual", [
        'student_id' => $student->id,
        'status' => 'sakit',
        'notes' => 'Surat dokter menyusul',
    ])->assertOk()
        ->assertJsonPath('data.status', 'sakit')
        ->assertJsonPath('data.check_in_method', 'manual_teacher')
        ->assertJsonPath('data.checked_in_at', null)
        ->assertJsonPath('data.notes', 'Surat dokter menyusul');

    expect(SessionAttendance::where('student_id', $student->id)->value('recorded_by'))->toBe($this->teacher->id);
});

test('absensi manual massal melaporkan item yang gagal tanpa membatalkan yang lain', function () {
    $id = ($this->startSession)()->json('data.id');

    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/attendance/bulk", [
        'attendances' => [
            ['student_id' => $this->students[0]->id, 'status' => 'hadir'],
            ['student_id' => $this->students[1]->id, 'status' => 'izin', 'notes' => 'Dispensasi lomba'],
            ['student_id' => $this->outsider->id, 'status' => 'hadir'],
        ],
    ])->assertOk()
        ->assertJsonPath('data.updated', 2)
        ->assertJsonPath('data.failed', 1)
        ->assertJsonPath('data.errors.0', "Siswa #{$this->outsider->id}: Siswa tidak terdaftar di kelas sesi ini.");

    $session = ClassSession::find($id);
    expect($session->attendances()->where('student_id', $this->students[0]->id)->value('status'))->toBe('hadir')
        ->and($session->attendances()->where('student_id', $this->students[1]->id)->value('status'))->toBe('izin');
});

test('absensi manual ditolak setelah sesi berakhir dan untuk status tak dikenal', function () {
    $id = ($this->startSession)()->json('data.id');

    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/attendance/manual", [
        'student_id' => $this->students[0]->id, 'status' => 'bolos',
    ])->assertStatus(422)->assertJsonPath('success', false)->assertJsonPath('data', null)->assertJsonStructure(['errors' => ['status']]);

    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/end")->assertOk();
    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/attendance/manual", [
        'student_id' => $this->students[0]->id, 'status' => 'hadir',
    ])->assertStatus(409);
});

// ── Akhiri kelas & auto-close (aturan 6, 10) ─────────────────────────────

test('mengakhiri kelas: alpha tetap final dan draf jurnal mengajar dibuat', function () {
    Bus::fake([SendFcmNotificationJob::class]);
    $id = ($this->startSession)()->json('data.id');
    ($this->scan)($this->students[0], ($this->qrToken)($id))->assertOk();
    $this->travelTo(Carbon::parse(MONDAY.' 08:25:00'));

    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/end", [
        'topic' => 'Limit fungsi aljabar',
        'notes' => 'Siswa aktif berdiskusi.',
    ])->assertOk()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.is_auto_closed', false)
        ->assertJsonPath('data.present_count', 1)
        ->assertJsonPath('data.absent_count', 2);

    $session = ClassSession::find($id);
    expect($session->actual_end->format('H:i'))->toBe('08:25')
        ->and($session->qr_secret)->toBeNull();

    $journal = TeachingJournal::sole();
    expect($session->teaching_journal_id)->toBe($journal->id)
        ->and($journal->status)->toBe('draft')
        ->and($journal->topic)->toBe('Limit fungsi aljabar')
        ->and($journal->students_present)->toBe(1)
        ->and($journal->students_absent)->toBe(2)
        ->and($journal->jam_ke)->toBe(1);

    Bus::assertDispatched(SendFcmNotificationJob::class, fn ($job) => str_contains($job->title, 'ALPHA') && count($job->userIds) === 2);

    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/end")
        ->assertStatus(409)->assertJsonPath('message', 'Sesi kelas sudah berakhir.');
});

test('scheduler menutup otomatis sesi yang lewat jam selesai + 5 menit', function () {
    Bus::fake([SendFcmNotificationJob::class]);
    $id = ($this->startSession)()->json('data.id');

    $this->travelTo(Carbon::parse(MONDAY.' 08:35:00'));
    $this->artisan('class-sessions:auto-close')->expectsOutput('Tidak ada sesi kelas yang perlu ditutup otomatis.')->assertSuccessful();
    expect(ClassSession::find($id)->status)->toBe('active');

    $this->travelTo(Carbon::parse(MONDAY.' 08:35:01'));
    $this->artisan('class-sessions:auto-close')->expectsOutput('1 sesi kelas ditutup otomatis.')->assertSuccessful();

    $session = ClassSession::find($id);
    expect($session->status)->toBe('auto_closed')
        ->and($session->is_auto_closed)->toBeTrue()
        ->and($session->attendances()->where('status', 'alpha')->count())->toBe(3)
        ->and(ActivityLog::where('event', 'class_session.auto_closed')->where('auditable_id', $id)->exists())->toBeTrue();

    Bus::assertDispatched(SendFcmNotificationJob::class, fn ($job) => $job->userIds === [$this->teacher->id]
        && str_contains($job->body, '3 siswa tercatat alpha'));
});

test('auto-close menutup semua sesi kedaluwarsa sekaligus', function () {
    ClassSession::factory()->count(3)->active()->create([
        'session_date' => MONDAY, 'scheduled_start' => '07:00:00', 'scheduled_end' => '07:45:00',
    ]);
    $this->travelTo(Carbon::parse(MONDAY.' 09:00:00'));

    $this->artisan('class-sessions:auto-close')->expectsOutput('3 sesi kelas ditutup otomatis.');
    expect(ClassSession::where('status', 'auto_closed')->count())->toBe(3);
});

// ── Koreksi Waka/TU/Admin (aturan 8, 9) ──────────────────────────────────

test('waka kurikulum mengoreksi alpha menjadi izin dengan alasan dan audit log', function () {
    $id = ($this->startSession)()->json('data.id');
    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/end")->assertOk();
    $waka = User::factory()->create(['role' => 'waka_kurikulum', 'name' => 'Bu Rina']);
    $attendance = SessionAttendance::where('student_id', $this->students[0]->id)->sole();

    $this->actingAs($waka)->putJson("/api/v1/admin/class-sessions/attendances/{$attendance->id}/override", [
        'status' => 'izin',
        'reason' => 'Surat izin orang tua diterima TU',
    ])->assertOk()
        ->assertJsonPath('data.status', 'izin')
        ->assertJsonPath('data.is_override', true)
        ->assertJsonPath('data.override_by', 'Bu Rina')
        ->assertJsonPath('data.override_reason', 'Surat izin orang tua diterima TU');

    $log = ActivityLog::where('event', 'session_attendance.override')->sole();
    expect($log->user_id)->toBe($waka->id)
        ->and($log->old_values)->toBe(['status' => 'alpha'])
        ->and($log->new_values['status'])->toBe('izin');
});

test('kehadiran yang tercatat tidak bisa dihapus lewat koreksi', function () {
    $id = ($this->startSession)()->json('data.id');
    ($this->scan)($this->students[0], ($this->qrToken)($id))->assertOk();
    $admin = User::factory()->create(['role' => 'admin']);
    $attendance = SessionAttendance::where('student_id', $this->students[0]->id)->sole();

    $this->actingAs($admin)->putJson("/api/v1/admin/class-sessions/attendances/{$attendance->id}/override", [
        'status' => 'alpha', 'reason' => 'Mencoba menghapus kehadiran',
    ])->assertStatus(422)->assertJsonStructure(['errors' => ['status']]);

    $this->actingAs($admin)->putJson("/api/v1/admin/class-sessions/attendances/{$attendance->id}/override", [
        'status' => 'sakit', 'reason' => 'Mencoba mengubah kehadiran',
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Presensi berstatus Hadir tidak dapat dikoreksi. Kehadiran yang sudah tercatat tidak boleh dihapus.')
        ->assertJsonPath('error_code', 'transition_not_allowed');

    expect($attendance->fresh()->status)->toBe('hadir');
});

test('koreksi wajib beralasan minimal 10 karakter dan hanya untuk peran berwenang', function () {
    ($this->startSession)()->assertCreated();
    $attendance = SessionAttendance::first();

    $this->actingAs(User::factory()->create(['role' => 'staf_tu']))
        ->putJson("/api/v1/admin/class-sessions/attendances/{$attendance->id}/override", ['status' => 'izin', 'reason' => 'izin'])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Alasan koreksi minimal 10 karakter.');

    $this->actingAs($this->teacher)
        ->putJson("/api/v1/admin/class-sessions/attendances/{$attendance->id}/override", ['status' => 'izin', 'reason' => 'Guru mencoba koreksi sendiri'])
        ->assertForbidden();

    expect($attendance->fresh()->is_override)->toBeFalse();
});

// ── Monitoring & rekap admin ─────────────────────────────────────────────

test('admin melihat daftar sesi terpaginasi dengan filter', function () {
    ($this->startSession)()->assertCreated();
    ClassSession::factory()->completed()->create(['session_date' => '2026-10-02']);
    $admin = User::factory()->create(['role' => 'waka_kurikulum']);

    $this->actingAs($admin)->getJson('/api/v1/admin/class-sessions?per_page=1')
        ->assertOk()
        ->assertJsonPath('data.total', 2)
        ->assertJsonPath('data.last_page', 2)
        ->assertJsonPath('data.current_page', 1)
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.session_date', MONDAY);

    $this->actingAs($admin)->getJson('/api/v1/admin/class-sessions?status=active&date='.MONDAY)
        ->assertOk()
        ->assertJsonPath('data.total', 1)
        ->assertJsonPath('data.items.0.status', 'active')
        ->assertJsonPath('data.items.0.total_students', 3);

    $this->actingAs($admin)->getJson('/api/v1/admin/class-sessions?status=bogus')
        ->assertStatus(422)->assertJsonPath('success', false);

    $id = ClassSession::where('status', 'active')->value('id');
    $this->actingAs($admin)->getJson("/api/v1/admin/class-sessions/{$id}/attendances")
        ->assertOk()->assertJsonCount(3, 'data');
});

test('rekap presensi per siswa, kelas, dan mapel', function () {
    $id = ($this->startSession)()->json('data.id');
    ($this->scan)($this->students[0], ($this->qrToken)($id))->assertOk(); // Citra hadir
    $this->travelTo(Carbon::parse(MONDAY.' 07:20:00'));
    ($this->scan)($this->students[1], ($this->qrToken)($id))->assertOk(); // Andi telat
    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/end")->assertOk(); // Budi alpha

    // A session that never took place must not count.
    ClassSession::factory()->create(['session_date' => MONDAY, 'classroom_id' => $this->classroom->id]);

    $admin = User::factory()->create(['role' => 'admin']);
    $query = 'start_date=2026-10-01&end_date=2026-10-31';

    $this->actingAs($admin)->getJson("/api/v1/admin/class-sessions/reports?{$query}&group_by=student")
        ->assertOk()
        ->assertJsonPath('data.summary.total_sessions', 1)
        ->assertJsonPath('data.summary.total_hadir', 1)
        ->assertJsonPath('data.summary.total_telat', 1)
        ->assertJsonPath('data.summary.total_alpha', 1)
        ->assertJsonPath('data.summary.average_presence_rate', 0.6667)
        ->assertJsonCount(3, 'data.details')
        ->assertJsonPath('data.details.0.student_name', 'Andi')
        ->assertJsonPath('data.details.0.telat', 1)
        ->assertJsonPath('data.details.0.presence_rate', 1)
        ->assertJsonPath('data.details.1.student_name', 'Budi')
        ->assertJsonPath('data.details.1.presence_rate', 0);

    $this->actingAs($admin)->getJson("/api/v1/admin/class-sessions/reports?{$query}&group_by=subject")
        ->assertJsonPath('data.details.0.subject_name', 'Matematika Peminatan')
        ->assertJsonPath('data.details.0.total', 3);

    $this->actingAs($admin)->getJson("/api/v1/admin/class-sessions/reports?{$query}&group_by=class")
        ->assertJsonPath('data.details.0.classroom_name', 'X-1 (IPA)')
        ->assertJsonPath('data.details.0.hadir', 1);

    $this->actingAs($admin)->getJson('/api/v1/admin/class-sessions/reports?start_date=2026-10-31&end_date=2026-10-01')
        ->assertStatus(422)->assertJsonPath('success', false);
});

test('kepala sekolah bisa membaca daftar, detail, dan rekap sesi tetapi tidak bisa mengoreksi', function () {
    $id = ($this->startSession)()->json('data.id');
    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$id}/end")->assertOk();
    $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);

    $this->actingAs($kepsek)->getJson('/api/v1/admin/class-sessions')->assertOk()->assertJsonPath('data.total', 1);
    $this->actingAs($kepsek)->getJson("/api/v1/admin/class-sessions/{$id}/attendances")->assertOk()->assertJsonCount(3, 'data');
    $this->actingAs($kepsek)->getJson('/api/v1/admin/class-sessions/reports?start_date=2026-10-01&end_date=2026-10-31')
        ->assertOk()->assertJsonPath('data.summary.total_alpha', 3);

    $attendance = SessionAttendance::first();
    $this->actingAs($kepsek)->putJson("/api/v1/admin/class-sessions/attendances/{$attendance->id}/override", [
        'status' => 'izin', 'reason' => 'Kepala sekolah mencoba koreksi',
    ])->assertForbidden();
    expect($attendance->fresh()->status)->toBe('alpha');
});

// ── Bahasa pengguna dan kode galat ──────────────────────────────────────
// The app used to tell refusals apart by looking for Indonesian phrases in
// the message. Messages now follow the user's language, so every refusal
// carries a stable error_code instead.

test('penolakan memakai bahasa pengguna dan membawa kode tetap', function () {
    $this->teacher->update(['preferred_locale' => 'en']);
    $this->travelTo(Carbon::parse(MONDAY.' 06:00:00'));

    ($this->startSession)()
        ->assertStatus(422)
        ->assertJsonPath('message', 'The class can start from 06:50.')
        ->assertJsonPath('error_code', 'outside_schedule_window');
});

test('siswa berbahasa arab mendapat penolakan scan dalam bahasa arab', function () {
    $id = ($this->startSession)()->json('data.id');
    $token = ($this->qrToken)($id);
    $student = $this->students[0];
    $student->user->update(['preferred_locale' => 'ar']);

    ($this->scan)($student, $token)->assertOk();
    ($this->scan)($student, $token)
        ->assertStatus(409)
        ->assertJsonPath('message', 'تم تسجيل حضورك في هذه الحصة بالفعل')
        ->assertJsonPath('error_code', 'already_checked_in');

    ($this->scan)($student, 'bukan-qr-sesi')
        ->assertJsonPath('error_code', 'qr_invalid');
});

test('setiap penolakan sesi kelas membawa error_code', function () {
    $source = file_get_contents(app_path('Services/Academic/ClassSessionService.php'));
    preg_match_all('/new ClassSessionException\((.*?)\);/s', $source, $throws);

    expect($throws[1])->not->toBeEmpty();
    foreach ($throws[1] as $args) {
        expect(preg_match("/errorCode: '|'[a-z_]+'\s*\)?\s*$/", trim($args)))->toBe(1, "Tanpa error_code: {$args}");
    }
});
