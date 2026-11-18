<?php

use App\Jobs\SendFcmNotificationJob;
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
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

/*
 * teacher reminders 5 minutes before a lesson starts / ends,
 * and the daily per-child attendance digest for parents.
 * Fixture: Monday 2026-10-05, schedule "jam 1–2" = 07:00–08:30.
 */

const REMINDER_DAY = '2026-10-05';

beforeEach(function () {
    Cache::flush();
    Bus::fake([SendFcmNotificationJob::class]);
    $this->travelTo(Carbon::parse(REMINDER_DAY.' 06:50:00'));

    $year = AcademicYear::factory()->create(['is_active' => true]);
    $preset = SchedulePreset::create(['name' => 'Reguler', 'is_active' => true]);
    foreach ([[1, '07:00', '07:45'], [2, '07:45', '08:30'], [3, '08:30', '09:15']] as [$n, $start, $end]) {
        ScheduleTime::create(['schedule_preset_id' => $preset->id, 'session_number' => $n, 'start_time' => $start, 'end_time' => $end]);
    }

    $this->teacher = User::factory()->create(['role' => 'guru']);
    $this->classroom = Classroom::factory()->create(['name' => 'X-1 (IPA)']);
    $this->subject = Subject::factory()->create(['name' => 'Matematika']);
    $this->students = collect(['Citra', 'Andi', 'Budi'])->map(fn ($name) => Student::factory()->create([
        'classroom_id' => $this->classroom->id,
        'user_id' => User::factory()->create(['role' => 'student', 'name' => $name])->id,
    ]));
    $this->schedule = Schedule::factory()->create([
        'academic_year_id' => $year->id,
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacher->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 2,
    ]);

    $this->at = fn (string $time) => $this->travelTo(Carbon::parse(REMINDER_DAY.' '.$time));
    $this->remind = fn () => $this->artisan('class-sessions:remind')->assertSuccessful();
    $this->pushesTo = fn (int $userId, string $needle) => Bus::dispatched(SendFcmNotificationJob::class,
        fn ($job) => $job->userIds === [$userId] && str_contains($job->title.' '.$job->body, $needle))->count();
});

test('guru diingatkan 5 menit sebelum kelas dimulai, sekali saja', function () {
    ($this->at)('06:54:59');
    ($this->remind)();
    expect(($this->pushesTo)($this->teacher->id, 'dimulai 5 menit lagi'))->toBe(0);

    ($this->at)('06:55:00');
    $this->artisan('class-sessions:remind')->expectsOutput('Pengingat terkirim: 1 akan dimulai, 0 akan berakhir.');

    ($this->at)('06:56:00');
    ($this->remind)();

    expect(($this->pushesTo)($this->teacher->id, 'Sesi Matematika X-1 (IPA) dimulai 5 menit lagi'))->toBe(1);
    $session = ClassSession::sole();
    expect($session->status)->toBe('scheduled')
        ->and($session->start_reminder_sent_at->format('H:i'))->toBe('06:55');
    Bus::assertDispatched(SendFcmNotificationJob::class, fn ($job) => $job->payload['type'] === 'class_session_start_reminder'
        && $job->payload['session_id'] === (string) $session->id
        && $job->channelId === 'attendance_alerts');
});

test('kelas yang sudah dimulai atau jadwal hari lain tidak diingatkan', function () {
    $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$this->schedule->id}/start")->assertCreated();
    Bus::fake([SendFcmNotificationJob::class]); // ignore the "session started" push to students

    ($this->at)('06:56:00');
    ($this->remind)();
    expect(Bus::dispatched(SendFcmNotificationJob::class)->count())->toBe(0);

    $this->schedule->update(['day' => 'Selasa']);
    ClassSession::query()->forceDelete();
    ($this->at)('06:57:00');
    ($this->remind)();
    expect(Bus::dispatched(SendFcmNotificationJob::class)->count())->toBe(0);
});

test('guru diingatkan 5 menit sebelum sesi aktif berakhir beserta jumlah siswa belum absen', function () {
    ($this->at)('07:05:00');
    $id = $this->actingAs($this->teacher)->postJson("/api/v1/teacher/class-sessions/{$this->schedule->id}/start")->json('data.id');
    $token = $this->actingAs($this->teacher)->getJson("/api/v1/teacher/class-sessions/{$id}/qr")->json('data.qr_token');
    $this->actingAs($this->students[0]->user)->postJson('/api/v1/student/class-session/scan-qr', ['qr_token' => $token])->assertOk();

    ($this->at)('08:24:59');
    ($this->remind)();
    expect(($this->pushesTo)($this->teacher->id, 'berakhir 5 menit lagi'))->toBe(0);

    ($this->at)('08:25:00');
    ($this->remind)();
    ($this->at)('08:26:00');
    ($this->remind)();

    expect(($this->pushesTo)($this->teacher->id, 'Sesi Matematika X-1 (IPA) berakhir 5 menit lagi 2 siswa belum absen.'))->toBe(1)
        ->and(ClassSession::find($id)->end_reminder_sent_at)->not->toBeNull();
});

test('rangkuman harian orang tua per anak berisi sesi yang terlaksana', function () {
    [$citra, $andi, $budi] = $this->students->all();
    $parentCitra = User::factory()->create(['role' => 'parent']);
    $parentAndi = User::factory()->create(['role' => 'orang_tua']);
    $citra->update(['parent_id' => $parentCitra->id]);
    $andi->update(['parent_id' => $parentAndi->id]);   // Budi has no parent account

    $fisika = Subject::factory()->create(['name' => 'Fisika']);
    $math = ClassSession::factory()->completed()->create([
        'classroom_id' => $this->classroom->id, 'subject_id' => $this->subject->id,
        'session_date' => REMINDER_DAY, 'jam_ke' => 1, 'scheduled_start' => '07:00:00', 'scheduled_end' => '08:30:00',
    ]);
    $physics = ClassSession::factory()->completed()->create([
        'classroom_id' => $this->classroom->id, 'subject_id' => $fisika->id,
        'session_date' => REMINDER_DAY, 'jam_ke' => 3, 'scheduled_start' => '08:30:00', 'scheduled_end' => '09:15:00',
    ]);
    $notHeld = ClassSession::factory()->create([ // still "scheduled": never took place
        'classroom_id' => $this->classroom->id, 'session_date' => REMINDER_DAY, 'jam_ke' => 5,
    ]);
    foreach ([
        [$math, $citra, 'hadir'], [$physics, $citra, 'telat'],
        [$math, $andi, 'alpha'], [$physics, $andi, 'alpha'],
        [$math, $budi, 'hadir'], [$notHeld, $andi, 'alpha'],
    ] as [$session, $student, $status]) {
        SessionAttendance::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'status' => $status]);
    }

    ($this->at)('17:00:00');
    $this->artisan('class-sessions:parent-digest')->expectsOutput('Rangkuman kehadiran 2026-10-05 terkirim ke 2 anak.');

    expect(($this->pushesTo)($parentAndi->id, 'Kehadiran Andi hari ini 0/2 sesi hadir. Alpha: Matematika (Jam ke-1), Fisika (Jam ke-3).'))->toBe(1)
        ->and(($this->pushesTo)($parentCitra->id, 'Kehadiran Citra hari ini 2/2 sesi hadir. Terlambat: 1.'))->toBe(1)
        ->and(Bus::dispatched(SendFcmNotificationJob::class)->count())->toBe(2);
});

test('rangkuman tanggal yang sama tidak terkirim dua kali kecuali --force', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $this->students[0]->update(['parent_id' => $parent->id]);
    $session = ClassSession::factory()->completed()->create(['classroom_id' => $this->classroom->id, 'session_date' => REMINDER_DAY]);
    SessionAttendance::create(['class_session_id' => $session->id, 'student_id' => $this->students[0]->id, 'status' => 'hadir']);
    ($this->at)('17:00:00');

    $this->artisan('class-sessions:parent-digest')->assertSuccessful();
    $this->artisan('class-sessions:parent-digest')
        ->expectsOutput('Rangkuman 2026-10-05 sudah pernah dikirim. Pakai --force untuk mengirim ulang.');
    $this->artisan('class-sessions:parent-digest --force')->assertSuccessful();
    $this->artisan('class-sessions:parent-digest --date=2026-10-04')
        ->expectsOutput('Rangkuman kehadiran 2026-10-04 terkirim ke 0 anak.');

    expect(Bus::dispatched(SendFcmNotificationJob::class)->count())->toBe(2);
});
