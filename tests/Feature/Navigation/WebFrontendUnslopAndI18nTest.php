<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ProfileChangeRequest;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Models\StudentMutation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

beforeEach(function () {
    Cache::flush();
    Notification::fake();
});

test('p5_projects navigation key has id en ar parity and appears once for authorized roles', function () {
    foreach (['id', 'en', 'ar'] as $locale) {
        $nav = require lang_path("{$locale}/navigation.php");
        expect($nav)->toHaveKey('p5_projects')
            ->and(trim((string) $nav['p5_projects']))->not->toBeEmpty();
    }

    foreach (['waka_kurikulum', 'koordinator_p5', 'guru', 'wali_kelas', 'kepala_sekolah', 'admin'] as $role) {
        $user = User::factory()->create(['role' => $role, 'must_change_password' => false])->fresh();
        $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();
        $sidebarHtml = Str::between($html, 'id="sidebar-nav"', '</nav>');
        expect(substr_count($sidebarHtml, 'href="'.route('p5.projects.index').'"'))->toBe(1, "p5.projects.index for {$role}");
    }

    foreach (['staf_tu', 'siswa'] as $unauthorizedRole) {
        $user = User::factory()->create(['role' => $unauthorizedRole, 'must_change_password' => false])->fresh();
        if ($unauthorizedRole === 'siswa') {
            Student::factory()->create(['user_id' => $user->id]);
        }
        $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();
        $sidebarHtml = Str::between($html, 'id="sidebar-nav"', '</nav>');
        expect(substr_count($sidebarHtml, 'href="'.route('p5.projects.index').'"'))->toBe(0, "p5.projects.index hidden for {$unauthorizedRole}");
    }
});

test('mutations index renders both approve and reject principal forms and translates into en and ar', function () {
    $schoolYear = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_ACTIVE]);
    $classroom = Classroom::factory()->create([
        'name' => 'XI-2',
        'grade_level' => 'XI',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
    ]);
    $student = Student::factory()->create([
        'classroom_id' => $classroom->id,
        'status' => 'active',
    ]);
    $tu = User::factory()->create(['role' => 'staf_tu', 'must_change_password' => false]);

    $mutation = StudentMutation::create([
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'school_year_id' => $schoolYear->id,
        'type' => StudentMutation::TYPE_OUT,
        'mutation_date' => '2026-11-15',
        'target_school' => 'SMA Negeri 8 Jakarta',
        'reason' => 'Pindah tugas orang tua',
        'status' => StudentMutation::STATUS_PENDING,
        'billing_review_status' => StudentMutation::BILLING_CLEARED,
        'requested_by' => $tu->id,
    ]);

    $kepsek = User::factory()->create([
        'role' => 'kepala_sekolah',
        'preferred_locale' => 'id',
        'must_change_password' => false,
    ])->fresh();

    $htmlId = $this->actingAs($kepsek)->get(route('mutations.index'))->assertOk()->getContent();
    expect($htmlId)
        ->toContain('action="'.route('mutations.approve', $mutation).'"')
        ->toContain('action="'.route('mutations.reject', $mutation).'"')
        ->toContain('name="reason"')
        ->toContain('Menunggu Kepala Sekolah');

    $kepsek->update(['preferred_locale' => 'en']);
    $this->actingAs($kepsek->fresh())
        ->withSession(['locale' => 'en'])
        ->get(route('mutations.index'))
        ->assertOk()
        ->assertSee('Student Transfer History & Approvals')
        ->assertSee('Awaiting Principal');

    $kepsek->update(['preferred_locale' => 'ar']);
    $this->actingAs($kepsek->fresh())
        ->withSession(['locale' => 'ar'])
        ->get(route('mutations.index'))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('سجل وموافقات انتقال الطلاب')
        ->assertSee('بانتظار مدير المدرسة');
});

test('parent and teacher leave request views display auto_approved and escalation badges accurately', function () {
    $academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $homeroom = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $parent = User::factory()->create(['role' => 'orang_tua', 'must_change_password' => false]);
    $classroom = Classroom::factory()->create([
        'name' => 'X-3',
        'academic_year' => '2026/2027',
        'teacher_id' => $homeroom->id,
    ]);
    $student = Student::factory()->create([
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
        'status' => 'active',
    ]);

    StudentLeaveRequest::create([
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $academicYear->id,
        'submitted_by' => $parent->id,
        'type' => StudentLeaveRequest::TYPE_SAKIT,
        'start_date' => '2026-10-14',
        'end_date' => '2026-10-14',
        'days_count' => 1,
        'reason' => 'Demam ringan',
        'status' => StudentLeaveRequest::STATUS_AUTO_APPROVED,
        'auto_approved' => true,
    ]);

    StudentLeaveRequest::create([
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $academicYear->id,
        'submitted_by' => $parent->id,
        'type' => StudentLeaveRequest::TYPE_IZIN,
        'start_date' => '2026-10-20',
        'end_date' => '2026-10-21',
        'days_count' => 2,
        'reason' => 'Acara keluarga luar kota',
        'status' => StudentLeaveRequest::STATUS_PENDING,
        'escalated_at' => now(),
    ]);

    $this->actingAs($parent)
        ->get(route('parent-portal.leave-requests.index'))
        ->assertOk()
        ->assertSee('Disetujui Otomatis');

    $this->actingAs($homeroom)
        ->get(route('teacher.leave-requests.index'))
        ->assertOk()
        ->assertSee('Disetujui Otomatis')
        ->assertSee('Eskalasi Kesiswaan');
});

test('profile change request views expose current recorded value preview and required rejection reason', function () {
    $studentUser = User::factory()->create([
        'role' => 'siswa',
        'name' => 'Nadia Putri',
        'must_change_password' => false,
    ]);
    $student = Student::factory()->create([
        'user_id' => $studentUser->id,
        'nisn' => '0099887766',
        'status' => 'active',
    ]);

    $this->actingAs($studentUser)
        ->get(route('profile.change-requests.index'))
        ->assertOk()
        ->assertSee('Tercatat saat ini')
        ->assertSee('0099887766');

    ProfileChangeRequest::create([
        'requester_user_id' => $studentUser->id,
        'target_user_id' => $studentUser->id,
        'target_type' => ProfileChangeRequest::TARGET_STUDENT,
        'target_id' => $student->id,
        'field_name' => 'nisn',
        'field_label' => 'NISN',
        'old_value' => '0099887766',
        'new_value' => '0099887799',
        'reason' => 'Koreksi digit terakhir sesuai ijazah SMP',
        'evidence_path' => 'profile-change-evidence/sample.pdf',
        'status' => ProfileChangeRequest::STATUS_PENDING,
    ]);

    $tu = User::factory()->create(['role' => 'staf_tu', 'must_change_password' => false]);
    $adminHtml = $this->actingAs($tu)
        ->get(route('admin.profile-change-requests.index'))
        ->assertOk()
        ->getContent();

    expect($adminHtml)
        ->toContain('name="review_notes" required')
        ->toContain('0099887799');
});

test('tahfidz dashboard renders content for canonical siswa orang_tua and kepala_sekolah roles without blank body', function () {
    AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => '2026-07-13',
        'end_date' => '2026-12-18',
    ]);

    $parent = User::factory()->create(['role' => 'orang_tua', 'must_change_password' => false]);
    $classroom = Classroom::factory()->create([
        'name' => 'X-Tahfidz',
        'academic_year' => '2026/2027',
    ]);
    $studentUser = User::factory()->create(['role' => 'siswa', 'name' => 'Ahmad Hafidz', 'must_change_password' => false]);
    Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
        'status' => 'active',
    ]);
    $kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'must_change_password' => false]);

    $this->actingAs($studentUser)
        ->get(route('tahfidz.dashboard'))
        ->assertOk()
        ->assertSee('Status Target Semester')
        ->assertSee('Ringkasan Setoran');

    $this->actingAs($parent)
        ->get(route('tahfidz.dashboard'))
        ->assertOk()
        ->assertSee('Status Target Semester')
        ->assertSee('Ahmad Hafidz');

    $this->actingAs($kepsek)
        ->get(route('tahfidz.dashboard'))
        ->assertOk()
        ->assertSee('Daftar Hafalan Siswa Kelas')
        ->assertSee('X-Tahfidz');
});

test('lang json files maintain key parity and unslopped dashboards contain zero decorative slop classes', function () {
    $idKeys = array_keys(json_decode(file_get_contents(lang_path('id.json')), true));
    $enKeys = array_keys(json_decode(file_get_contents(lang_path('en.json')), true));
    $arKeys = array_keys(json_decode(file_get_contents(lang_path('ar.json')), true));

    expect($idKeys)->toBe($enKeys)
        ->and($idKeys)->toBe($arKeys);

    $dashboards = [
        resource_path('views/workflows/dashboard.blade.php'),
        resource_path('views/analytics/index.blade.php'),
        resource_path('views/uks/dashboard.blade.php'),
        resource_path('views/counseling/dashboard.blade.php'),
        resource_path('views/tahfidz/dashboard.blade.php'),
    ];

    foreach ($dashboards as $path) {
        $content = file_get_contents($path);
        expect($content)
            ->not->toContain('rounded-[2.5rem]')
            ->not->toContain('hover:scale-105')
            ->not->toContain('from-purple-')
            ->not->toContain('fuchsia-')
            ->not->toContain('Glassmorphic');
    }
});
