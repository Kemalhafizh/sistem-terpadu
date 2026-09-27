<?php

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\ParentMessage;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Events\ChatMessageSent;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // Buat tahun akademik aktif
    $this->academicYear = AcademicYear::create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    // Buat data master
    $this->classroom = Classroom::factory()->create(['grade_level' => 'XI']);
    $this->subject = Subject::factory()->create();

    // Buat Siswa & Akun Siswa
    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    // Buat Wali Murid & Hubungkan Anak
    $this->parentUser = User::factory()->create(['role' => 'parent']);
    $this->student->update(['parent_id' => $this->parentUser->id]);

    // Buat Guru
    $this->teacherUser = User::factory()->create(['role' => 'guru']);

    // Buat Jadwal Pelajaran
    $this->schedule = Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->teacherUser->id,
        'day' => 'Senin',
        'session_start' => '07:30',
        'session_end' => '09:00',
    ]);
});

// ── 1. CUSTOM MIDDLEWARE & VERSION HEADER ────────────────────────────────────

test('setiap respon API v1 menyertakan header X-API-Version v1.0', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/student/profile');

    $response->assertStatus(200);
    $response->assertHeader('X-API-Version', 'v1.0');
});

// ── 2. SISWA SCOPED ENDPOINTS ────────────────────────────────────────────────

test('siswa dapat mengakses profil diri sendiri', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/student/profile');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.uuid', $this->student->uuid)
        ->assertJsonPath('data.classroom', $this->classroom->name);
});

test('siswa dapat mengakses jadwal pelajaran mereka', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/student/schedule');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => [['id', 'day', 'subject_name', 'classroom_name']]]);
});

test('siswa dapat mengambil daftar nilai mereka', function () {
    Sanctum::actingAs($this->studentUser);

    Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'tugas',
        'score' => 85.50,
        'description' => 'Tugas Aljabar',
        'recorded_by' => $this->teacherUser->id,
    ]);

    $response = $this->getJson('/api/v1/student/grades');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => [['score', 'subject']]]);
});

test('siswa dapat mengambil data absensi mereka', function () {
    Sanctum::actingAs($this->studentUser);

    Attendance::create([
        'student_id' => $this->student->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'date' => '2026-05-27',
        'status' => 'H',
        'recorded_by' => $this->teacherUser->id,
    ]);

    $response = $this->getJson('/api/v1/student/attendance');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => [['status', 'date']]])
        ->assertJsonPath('data.0.status', 'H');
});

test('siswa dapat mengambil tagihan keuangan mereka', function () {
    Sanctum::actingAs($this->studentUser);

    $paymentCategory = \App\Models\PaymentCategory::create([
        'name' => 'SPP',
        'default_amount' => 150000,
        'is_monthly' => true,
    ]);

    Billing::create([
        'student_id' => $this->student->id,
        'payment_category_id' => $paymentCategory->id,
        'academic_year_id' => $this->academicYear->id,
        'month' => 'Mei',
        'amount' => 150000,
        'status' => 'unpaid',
        'due_date' => '2026-05-31',
    ]);

    $response = $this->getJson('/api/v1/student/billings');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => [['amount', 'status']]]);
});

test('siswa dapat mengambil log poin BK mereka', function () {
    Sanctum::actingAs($this->studentUser);

    $pointCategory = \App\Models\PointCategory::create([
        'name' => 'Keterlambatan',
        'type' => 'penalty',
        'default_points' => 10,
        'is_active' => true,
    ]);

    StudentPointLog::create([
        'student_id' => $this->student->id,
        'point_category_id' => $pointCategory->id,
        'points' => 10,
        'type' => 'penalty',
        'description' => 'Terlambat masuk sekolah',
        'created_by' => $this->teacherUser->id,
    ]);

    $response = $this->getJson('/api/v1/student/points');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => [['points']]]);
});

// ── 3. ORANG TUA SCOPED ENDPOINTS ────────────────────────────────────────────

test('orang tua dapat mendaftar anak-anaknya', function () {
    Sanctum::actingAs($this->parentUser);

    $response = $this->getJson('/api/v1/parent/children');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.uuid', $this->student->uuid);
});

test('orang tua dapat melihat ringkasan performa anaknya', function () {
    Sanctum::actingAs($this->parentUser);

    Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'pts',
        'score' => 90,
        'recorded_by' => $this->teacherUser->id,
    ]);

    Attendance::create([
        'student_id' => $this->student->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'date' => '2026-05-27',
        'status' => 'H',
        'recorded_by' => $this->teacherUser->id,
    ]);

    $response = $this->getJson("/api/v1/parent/child/{$this->student->uuid}/summary");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.statistics.average_grade', 90)
        ->assertJsonPath('data.statistics.attendance_rate', 100);
});

test('orang tua ditolak mengakses ringkasan siswa lain', function () {
    Sanctum::actingAs($this->parentUser);

    $otherStudent = Student::factory()->create(); // bukan anaknya

    $response = $this->getJson("/api/v1/parent/child/{$otherStudent->uuid}/summary");

    $response->assertStatus(403)
        ->assertJsonPath('success', false);
});

test('orang tua dapat mengambil data detail nilai dan tagihan anak mereka', function () {
    Sanctum::actingAs($this->parentUser);

    $response = $this->getJson("/api/v1/parent/child/{$this->student->uuid}/grades");
    $response->assertStatus(200);

    $response2 = $this->getJson("/api/v1/parent/child/{$this->student->uuid}/billings");
    $response2->assertStatus(200);
});

test('orang tua ditolak mengakses detail nilai siswa lain', function () {
    Sanctum::actingAs($this->parentUser);

    $otherStudent = Student::factory()->create();

    $response = $this->getJson("/api/v1/parent/child/{$otherStudent->uuid}/grades");
    $response->assertStatus(403);
});

test('orang tua dapat berkonsultasi via chat dan memicu event ChatMessageSent', function () {
    Event::fake();
    Sanctum::actingAs($this->parentUser);

    // student_uuid, not the numeric id — the app only ever learns a child's uuid.
    $response = $this->postJson('/api/v1/parent/messages', [
        'recipient_id' => $this->teacherUser->id,
        'student_uuid' => $this->student->uuid,
        'message' => 'Halo Bapak/Ibu Guru, bagaimana prestasi belajar anak saya hari ini?',
    ]);

    // Was a non-standard 210 in both the controller and this test.
    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.message', 'Halo Bapak/Ibu Guru, bagaimana prestasi belajar anak saya hari ini?');

    Event::assertDispatched(ChatMessageSent::class);
});

test('guru dapat membalas pesan wali murid dengan status 201', function () {
    Event::fake();

    // A reply is allowed once a thread about this student exists.
    Sanctum::actingAs($this->parentUser);
    $this->postJson('/api/v1/parent/messages', [
        'recipient_id' => $this->teacherUser->id,
        'student_uuid' => $this->student->uuid,
        'message' => 'Assalamualaikum Bapak/Ibu Guru.',
    ])->assertStatus(201);

    Sanctum::actingAs($this->teacherUser);
    $this->postJson('/api/v1/teacher/messages', [
        'student_uuid' => $this->student->uuid,
        'message' => 'Waalaikumsalam, ananda baik-baik saja.',
    ])->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.message', 'Waalaikumsalam, ananda baik-baik saja.');
});

// ── 4. GURU SCOPED ENDPOINTS ─────────────────────────────────────────────────

test('guru dapat mengambil jadwal mengajar mereka sendiri', function () {
    Sanctum::actingAs($this->teacherUser);

    $response = $this->getJson('/api/v1/teacher/schedule');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.subject_name', $this->subject->name);
});

test('guru dapat menginput absensi kehadiran siswa secara bulk', function () {
    Sanctum::actingAs($this->teacherUser);

    $response = $this->postJson('/api/v1/teacher/attendance', [
        'classroom_id' => $this->classroom->id,
        'date' => '2026-05-27',
        'schedule_id' => $this->schedule->id,
        'students' => [
            [
                'student_id' => $this->student->id,
                'status' => 'H',
                'notes' => 'Hadir tepat waktu',
            ]
        ]
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.recorded_count', 1);

    $this->assertDatabaseHas('attendances', [
        'student_id' => $this->student->id,
        'classroom_id' => $this->classroom->id,
        'status' => 'H',
    ]);
});

test('guru dapat menginput jurnal mengajar digital baru', function () {
    Sanctum::actingAs($this->teacherUser);

    $response = $this->postJson('/api/v1/teacher/journals', [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teaching_date' => '2026-05-27',
        'jam_ke' => 2,
        'topic' => 'Sistem Persamaan Linier Dua Variabel',
        'learning_activity' => 'Melakukan pemodelan SPLDV dengan masalah kontekstual.',
        'learning_method' => 'Diskusi Kelompok & Penemuan Terbimbing',
        'students_present' => 34,
        'students_absent' => 0,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.topic', 'Sistem Persamaan Linier Dua Variabel');

    $this->assertDatabaseHas('teaching_journals', [
        'user_id' => $this->teacherUser->id,
        'topic' => 'Sistem Persamaan Linier Dua Variabel',
    ]);
});

// ── 5. GENERAL ENDPOINTS ─────────────────────────────────────────────────────

test('pengguna umum dapat mengambil pengumuman ter-filter', function () {
    Sanctum::actingAs($this->studentUser);

    Announcement::factory()->create([
        'is_active' => true,
        'target_role' => 'student',
        'created_by' => User::factory()->create(['role' => 'admin'])->id,
    ]);

    $response = $this->getJson('/api/v1/announcements');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data']);
});

test('pengguna umum dapat mengambil data kalender akademik', function () {
    Sanctum::actingAs($this->studentUser);

    AcademicEvent::create([
        'title' => 'Ujian Tengah Semester',
        'description' => 'Ujian Tengah Semester Ganjil',
        'event_type' => 'academic',
        'start_date' => '2026-06-01 07:00:00',
        'end_date' => '2026-06-05 13:00:00',
        'is_all_day' => false,
        'color' => '#3b82f6',
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->teacherUser->id,
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/calendar');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => [['uuid', 'title', 'event_type']]]);
});

test('api endpoints are throttled when rate limit is exceeded', function () {
    Sanctum::actingAs($this->studentUser);

    // Make 60 requests to check global throttle limit
    for ($i = 0; $i < 60; $i++) {
        $response = $this->getJson('/api/v1/student/profile');
        $response->assertStatus(200);
    }

    // The 61st request must be rate limited (429)
    $response = $this->getJson('/api/v1/student/profile');
    $response->assertStatus(429);
});
