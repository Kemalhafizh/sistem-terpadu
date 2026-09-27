<?php

use App\Jobs\SendWhatsappBroadcastJob;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\DailyAssessment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappBroadcast;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use App\Services\AttendanceService;
use App\Services\DailyAssessmentService;
use App\Services\UksService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Create users with different roles
    $this->admin = User::factory()->create(['role' => 'admin', 'phone_number' => '081234567890']);
    $this->superadmin = User::factory()->create(['role' => 'superadmin', 'phone_number' => '081234567891']);
    $this->guru = User::factory()->create(['role' => 'guru', 'phone_number' => '081234567892']);
    $this->parent = User::factory()->create(['role' => 'parent', 'phone_number' => '081234567893']);

    // Create supporting records
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create(['teacher_id' => $this->guru->id]);
    $this->student = Student::factory()->create([
        'classroom_id' => $this->classroom->id,
        'parent_id' => $this->parent->id,
    ]);
});

// ── 1. ROLE-BASED ACCESS CONTROL (RBAC) ─────────────────────────────────

test('authorized users can access whatsapp gateway index and dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.whatsapp.index'));
    $response->assertStatus(200);

    $response = $this->actingAs($this->admin)->get(route('admin.whatsapp.dashboard'));
    $response->assertStatus(200);
});

test('unauthorized users are forbidden from whatsapp gateway endpoints', function () {
    $response = $this->actingAs($this->guru)->get(route('admin.whatsapp.index'));
    $response->assertStatus(403);

    $response = $this->actingAs($this->guru)->get(route('admin.whatsapp.dashboard'));
    $response->assertStatus(403);
});

// ── 2. TEMPLATES CRUD ──────────────────────────────────────────────────

test('admin can manage whatsapp templates', function () {
    // 1. Create Template
    $response = $this->actingAs($this->admin)->post(route('admin.whatsapp.templates.store'), [
        'name' => 'Template Pengumuman Nilai',
        'category' => 'akademik',
        'content' => 'Ananda {nama_siswa} mendapat nilai {nilai} untuk {mata_pelajaran}. KKM adalah {kkm}.',
    ]);

    $response->assertRedirect(route('admin.whatsapp.templates.index'));
    $this->assertDatabaseHas('whatsapp_templates', [
        'name' => 'Template Pengumuman Nilai',
        'category' => 'akademik',
    ]);

    $template = WhatsappTemplate::where('name', 'Template Pengumuman Nilai')->first();
    expect($template->placeholders)->toBe(['nama_siswa', 'nilai', 'mata_pelajaran', 'kkm']);

    // 2. Update Template
    $response = $this->actingAs($this->admin)->put(route('admin.whatsapp.templates.update', $template->id), [
        'name' => 'Template Pengumuman Nilai Baru',
        'category' => 'akademik',
        'content' => 'Ananda {nama_siswa} mendapat nilai {nilai} saja.',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.whatsapp.templates.index'));
    $this->assertDatabaseHas('whatsapp_templates', [
        'id' => $template->id,
        'name' => 'Template Pengumuman Nilai Baru',
    ]);
    expect($template->fresh()->placeholders)->toBe(['nama_siswa', 'nilai']);

    // 3. Delete Template
    $response = $this->actingAs($this->admin)->delete(route('admin.whatsapp.templates.delete', $template->id));
    $response->assertRedirect(route('admin.whatsapp.templates.index'));
    $this->assertDatabaseMissing('whatsapp_templates', ['id' => $template->id]);
});

// ── 3. BROADCAST MANAGEMENT ──────────────────────────────────────────

test('admin can create and queue instant broadcasts', function () {
    Queue::fake();

    $template = WhatsappTemplate::create([
        'name' => 'Broadcast Wali',
        'category' => 'umum',
        'content' => 'Halo Wali Murid {nama_orang_tua}.',
        'placeholders' => ['nama_orang_tua'],
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.whatsapp.broadcasts.store'), [
        'title' => 'Pengumuman Rapat Wali',
        'template_id' => $template->id,
        'target_type' => 'all_parents',
    ]);

    $response->assertRedirect(route('admin.whatsapp.broadcasts.index'));
    $this->assertDatabaseHas('whatsapp_broadcasts', [
        'title' => 'Pengumuman Rapat Wali',
        'status' => 'sending',
    ]);

    $broadcast = WhatsappBroadcast::where('title', 'Pengumuman Rapat Wali')->first();
    Queue::assertPushed(SendWhatsappBroadcastJob::class, function ($job) use ($broadcast) {
        return $job->broadcast->id === $broadcast->id;
    });
});

test('admin can schedule future broadcasts', function () {
    Queue::fake();

    $template = WhatsappTemplate::create([
        'name' => 'Broadcast Besok',
        'category' => 'umum',
        'content' => 'Halo.',
    ]);

    $futureTime = Carbon::now()->addDay();

    $response = $this->actingAs($this->admin)->post(route('admin.whatsapp.broadcasts.store'), [
        'title' => 'Pengumuman Terjadwal',
        'template_id' => $template->id,
        'target_type' => 'all_teachers',
        'scheduled_at' => $futureTime->format('Y-m-d H:i:s'),
    ]);

    $response->assertRedirect(route('admin.whatsapp.broadcasts.index'));
    $this->assertDatabaseHas('whatsapp_broadcasts', [
        'title' => 'Pengumuman Terjadwal',
        'status' => 'scheduled',
        'scheduled_at' => $futureTime->toDateTimeString(),
    ]);

    Queue::assertNotPushed(SendWhatsappBroadcastJob::class);
});

// ── 4. AUTOMATED TRIGGERS (HOOKS) ────────────────────────────────────

test('triggers attendance_absent instant notification when student is marked alpa', function () {
    $template = WhatsappTemplate::create([
        'name' => 'Template Alpa',
        'category' => 'akademik',
        'content' => 'Siswa {nama_siswa} alpa pada {tanggal}.',
        'placeholders' => ['nama_siswa', 'tanggal'],
    ]);

    WhatsappAutomation::create([
        'name' => 'Auto Alpa',
        'trigger' => 'attendance_absent',
        'template_id' => $template->id,
        'is_active' => true,
    ]);

    // Perform attendance record action
    $attendanceService = app(AttendanceService::class);
    $attendanceService->recordBulkAttendance($this->classroom->id, Carbon::today()->toDateString(), [
        $this->student->id => [
            'status' => 'A',
            'notes' => 'Tanpa keterangan',
        ],
    ], $this->guru->id);

    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '6281234567893', // Normalized parent number
        'type' => 'StudentAttendanceNotification',
        'status' => 'sent',
    ]);

    $log = WhatsappLog::where('recipient', '6281234567893')->where('type', 'StudentAttendanceNotification')->first();
    expect($log->message)->toContain($this->student->name);
    expect($log->message)->toContain(date('d-m-Y'));
});

test('triggers uks_visit_severe instant notification when severe visit is recorded', function () {
    $template = WhatsappTemplate::create([
        'name' => 'Template UKS Parah',
        'category' => 'kegiatan',
        'content' => 'Ananda {nama_siswa} sakit {keluhan} dengan gejala {gejala}.',
        'placeholders' => ['nama_siswa', 'keluhan', 'gejala'],
    ]);

    WhatsappAutomation::create([
        'name' => 'Auto UKS Darurat',
        'trigger' => 'uks_visit_severe',
        'template_id' => $template->id,
        'is_active' => true,
    ]);

    $uksService = app(UksService::class);
    $visit = $uksService->recordVisit([
        'student_id' => $this->student->id,
        'visit_time' => Carbon::now(),
        'complaint' => 'Asma Kambuh Sesak Napas',
        'diagnosis' => 'Serangan Asma Akut',
        'treatment' => 'Inhaler / Oksigen',
        'severity' => 'berat',
        'disposition' => 'rujuk_rs',
    ]);

    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '6281234567893',
        'type' => 'uks_emergency',
        'status' => 'sent',
    ]);

    $log = WhatsappLog::where('recipient', '6281234567893')->where('type', 'uks_emergency')->first();
    expect($log->message)->toContain($this->student->name);
    expect($log->message)->toContain('Asma Kambuh');
    expect($log->message)->toContain('berat');
});

test('triggers grade_below_kkm instant notification when score is below KKM', function () {
    $template = WhatsappTemplate::create([
        'name' => 'Template Remedial',
        'category' => 'akademik',
        'content' => 'Nilai {nama_siswa} adalah {nilai} di bawah KKM {kkm} pada {judul_penilaian}.',
        'placeholders' => ['nama_siswa', 'nilai', 'kkm', 'judul_penilaian'],
    ]);

    WhatsappAutomation::create([
        'name' => 'Auto Nilai Rendah',
        'trigger' => 'grade_below_kkm',
        'template_id' => $template->id,
        'is_active' => true,
    ]);

    $subject = Subject::factory()->create();
    $assessment = DailyAssessment::create([
        'subject_id' => $subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Ulangan Harian Matematika',
        'type' => 'ulangan_harian',
        'assessment_date' => Carbon::today(),
        'max_score' => 100,
        'kkm' => 75.0,
    ]);

    $assessmentService = app(DailyAssessmentService::class);
    $assessmentService->batchStoreScores($assessment->id, [
        $this->student->id => [
            'score' => 50,
            'notes' => 'Butuh bimbingan',
        ],
    ]);

    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '6281234567893',
        'type' => 'automation_grade_below_kkm',
        'status' => 'sent',
    ]);

    $log = WhatsappLog::where('recipient', '6281234567893')->where('type', 'automation_grade_below_kkm')->first();
    expect($log->message)->toContain($this->student->name);
    expect($log->message)->toContain('50');
    expect($log->message)->toContain('75');
    expect($log->message)->toContain('Ulangan Harian Matematika');
});
