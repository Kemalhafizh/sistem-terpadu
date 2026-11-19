<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\DapodikSyncItem;
use App\Models\Grade;
use App\Models\IntegrationConfig;
use App\Models\IntegrationLog;
use App\Models\ProfileChangeRequest;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Cache::flush();
    Notification::fake();

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->stafTu = User::factory()->create(['role' => 'staf_tu', 'name' => 'Staf TU Uji']);
    $this->wakaKurikulum = User::factory()->create(['role' => 'waka_kurikulum', 'name' => 'Waka Kurikulum Uji']);
    $this->homeroomTeacher = User::factory()->create(['role' => 'guru', 'name' => 'Wali Kelas X-A']);
    $this->waliKelasRoleUser = User::factory()->create(['role' => 'wali_kelas', 'name' => 'User Wali Kelas']);
    $this->parentUser = User::factory()->create(['role' => 'parent', 'name' => 'Orang Tua Uji']);
    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Siswa Uji']);

    $this->classroom = Classroom::factory()->create([
        'name' => 'X-A',
        'grade_level' => 'X',
        'academic_year' => '2026/2027',
        'teacher_id' => $this->homeroomTeacher->id,
    ]);

    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'parent_id' => $this->parentUser->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '260001',
        'nisn' => '0055001122',
        'status' => 'active',
    ]);
});

test('new navigation translation keys have full id en ar parity', function () {
    $keys = [
        'import_export_hub',
        'scheduled_reports',
        'gov_integrations',
        'student_accounts',
        'leave_requests_parent',
        'leave_requests_teacher',
        'profile_change_requests',
        'admin_profile_changes',
        'dapodik_sync_queue',
    ];

    $idNav = require lang_path('id/navigation.php');
    $enNav = require lang_path('en/navigation.php');
    $arNav = require lang_path('ar/navigation.php');

    foreach ($keys as $key) {
        expect($idNav)->toHaveKey($key)
            ->and($enNav)->toHaveKey($key)
            ->and($arNav)->toHaveKey($key)
            ->and(trim((string) $idNav[$key]))->not->toBeEmpty()
            ->and(trim((string) $enNav[$key]))->not->toBeEmpty()
            ->and(trim((string) $arNav[$key]))->not->toBeEmpty();
    }
});

test('sidebar exposes newly wired fase 2b links per role without 403s or duplicates and binds tahfidz uuid', function () {
    // Parent role sees leave requests, profile change requests, and UUID tahfidz link
    $parentPage = $this->actingAs($this->parentUser)
        ->get(route('parent-portal.leave-requests.index'))
        ->assertOk();

    $parentHtml = $parentPage->getContent();
    expect($parentHtml)
        ->toContain(route('parent-portal.leave-requests.index'))
        ->toContain(route('profile.change-requests.index'))
        ->toContain(route('tahfidz.show', $this->student))
        ->not->toContain(route('admin.integrations.index'))
        ->not->toContain(route('admin.profile-change-requests.index'));

    // Student role sees profile change requests and UUID tahfidz link
    $studentPage = $this->actingAs($this->studentUser)
        ->get(route('student.academic'))
        ->assertOk();

    $studentHtml = $studentPage->getContent();
    expect($studentHtml)
        ->toContain(route('profile.change-requests.index'))
        ->toContain(route('tahfidz.show', $this->student))
        ->not->toContain(route('student-accounts.index'))
        ->not->toContain(route('admin.integrations.index'));

    // Homeroom teacher sees student accounts and homeroom leave requests
    $teacherPage = $this->actingAs($this->homeroomTeacher)
        ->get(route('teacher.leave-requests.index'))
        ->assertOk();

    $teacherHtml = $teacherPage->getContent();
    expect($teacherHtml)
        ->toContain(route('student-accounts.index'))
        ->toContain(route('teacher.leave-requests.index'))
        ->toContain(route('profile.change-requests.index'));

    $this->actingAs($this->homeroomTeacher)->get(route('student-accounts.index'))->assertOk();
    $this->actingAs($this->waliKelasRoleUser)->get(route('teacher.leave-requests.index'))->assertOk();

    // Staf TU sees un-nested integration, import-export, scheduled reports, dapodik queue, and admin profile change links (single href in #sidebar-nav)
    $tuPage = $this->actingAs($this->stafTu)
        ->get(route('admin.profile-change-requests.index'))
        ->assertOk();

    $tuHtml = $tuPage->getContent();
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$tuHtml);
    $sidebarNode = $dom->getElementById('sidebar-nav');
    expect($sidebarNode)->not->toBeNull();
    $sidebarHtml = $dom->saveHTML($sidebarNode);

    foreach ([
        route('import-export.index'),
        route('admin.scheduled-reports.index'),
        route('admin.integrations.index'),
        route('admin.integrations.dapodik.sync-queue'),
        route('admin.profile-change-requests.index'),
        route('student-accounts.index'),
    ] as $url) {
        expect($sidebarHtml)->toContain($url);
        expect(substr_count($sidebarHtml, 'href="'.$url.'"'))->toBe(1);
    }
});

test('parent portal dashboard and student academic views use uuid routes real grade columns and clean ui', function () {
    $subject = Subject::factory()->create(['name' => 'Matematika Wajib', 'code' => 'MTK', 'category' => 'Nasional', 'kkm' => 76]);
    Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh',
        'score' => 92,
        'description' => 'Evaluasi Trigonometri',
        'recorded_by' => $this->homeroomTeacher->id,
    ]);

    $this->actingAs($this->parentUser)
        ->get(route('parent-portal.dashboard', ['child_id' => $this->student->id]))
        ->assertOk()
        ->assertSee(route('parent-portal.report-card', $this->student), false)
        ->assertSee(route('parent-portal.daily-assessments', $this->student), false)
        ->assertSee(route('parent-portal.schedule', $this->student), false)
        ->assertSee(route('parent-portal.attendance', $this->student), false)
        ->assertSee(route('parent-portal.billings', $this->student), false)
        ->assertSee(route('parent-portal.leave-requests.index'), false)
        ->assertSee(route('students.ledger', $this->student), false)
        ->assertDontSee('blur-3xl')
        ->assertDontSee('Portal Orang Tua <span', false);

    $this->actingAs($this->studentUser)
        ->get(route('student.academic'))
        ->assertOk()
        ->assertSee('Matematika Wajib')
        ->assertSee('Evaluasi Trigonometri')
        ->assertSee('Jenis Penilaian')
        ->assertSee(route('students.ledger', $this->student), false);
});

test('un-slopped integration views report cards profile change and whatsapp reset render cleanly with uuid webhooks', function () {
    $config = IntegrationConfig::firstOrCreate(
        ['provider' => 'dapodik'],
        ['name' => 'Koneksi Dapodik SMA ISA 1', 'is_active' => true]
    );

    IntegrationLog::create([
        'config_id' => $config->id,
        'direction' => 'export',
        'status' => 'success',
        'records_processed' => 12,
        'records_failed' => 0,
        'summary' => 'Ekspor uji berhasil',
    ]);

    $webhook = Webhook::create([
        'name' => 'Webhook Yayasan Uji',
        'url' => 'https://example.com/webhook',
        'secret' => 'secret-123',
        'events' => ['billing.paid'],
        'is_active' => true,
    ]);

    DapodikSyncItem::create([
        'entity_type' => 'student',
        'entity_id' => $this->student->id,
        'field_name' => 'nisn',
        'dapodik_column_label' => 'NISN',
        'old_value' => '0055001122',
        'new_value' => '0055009999',
        'status' => DapodikSyncItem::STATUS_PENDING,
    ]);

    ProfileChangeRequest::create([
        'requester_user_id' => $this->studentUser->id,
        'target_type' => 'student',
        'target_id' => $this->student->id,
        'target_user_id' => $this->studentUser->id,
        'field_name' => 'address',
        'field_label' => 'Alamat KK',
        'old_value' => 'Jl. Lama No. 1',
        'new_value' => 'Jl. Baru No. 9',
        'reason' => 'Pindah domisili sesuai KK terbaru',
        'status' => ProfileChangeRequest::STATUS_PENDING,
    ]);

    $this->actingAs($this->stafTu)
        ->get(route('admin.integrations.index'))
        ->assertOk()
        ->assertDontSee('bg-gray-950')
        ->assertDontSee('blur-2xl');

    $this->actingAs($this->stafTu)
        ->get(route('admin.integrations.dapodik'))
        ->assertOk()
        ->assertDontSee('bg-gray-950');

    $this->actingAs($this->stafTu)
        ->get(route('admin.integrations.emis'))
        ->assertOk()
        ->assertDontSee('bg-gray-950');

    $this->actingAs($this->stafTu)
        ->get(route('admin.integrations.logs'))
        ->assertOk()
        ->assertDontSee('bg-gray-950');

    $this->actingAs($this->studentUser)
        ->get(route('students.ledger', $this->student))
        ->assertOk()
        ->assertSee(route('student.academic'), false);

    $this->actingAs($this->stafTu)
        ->get(route('admin.integrations.webhooks.index'))
        ->assertOk()
        ->assertDontSee('bg-gray-950')
        ->assertSee(route('admin.integrations.webhooks.destroy', $webhook), false)
        ->assertSee(route('admin.integrations.webhooks.test', $webhook), false)
        ->assertSee($webhook->uuid);

    $this->actingAs($this->stafTu)
        ->get(route('admin.integrations.dapodik.sync-queue'))
        ->assertOk()
        ->assertSee('Menunggu');

    $this->actingAs($this->stafTu)
        ->get(route('admin.profile-change-requests.index'))
        ->assertOk()
        ->assertSee('Menunggu');

    $this->actingAs($this->studentUser)
        ->get(route('profile.change-requests.index'))
        ->assertOk()
        ->assertSee('Menunggu');

    $this->actingAs($this->wakaKurikulum)
        ->get(route('report-cards.index', ['classroom_id' => $this->classroom->id]))
        ->assertOk()
        ->assertSee('Dasbor Kelengkapan Input Nilai');

    Setting::set('whatsapp_otp_enabled', '1');
    $this->get(route('password.whatsapp.request'))
        ->assertOk()
        ->assertSee('Langkah 1')
        ->assertSee('Langkah 2');
});
