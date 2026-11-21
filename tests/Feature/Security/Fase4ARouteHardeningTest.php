<?php

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\CertificateTemplate;
use App\Models\Classroom;
use App\Models\IssuedCertificate;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;

test('config route_access tidak lagi menyisakan unguarded_debt', function () {
    expect(config('route_access.unguarded_debt'))->toBe([]);
});

test('non-staff ditolak 403 pada endpoint staf, guru, dan admin yang telah diamankan', function (string $role) {
    $user = User::factory()->create(['role' => $role]);
    $classroom = Classroom::factory()->create();

    $this->actingAs($user)->post(route('notifications.test_dispatch'))->assertForbidden();
    $this->actingAs($user)->get(route('tahfidz.bulk-input'))->assertForbidden();
    $this->actingAs($user)->get(route('tahfidz.target-setting'))->assertForbidden();
    $this->actingAs($user)->get(route('mutabaah.rekap-kelas', $classroom->id))->assertForbidden();
    $this->actingAs($user)->get(route('event-attendance.create'))->assertForbidden();
    $this->actingAs($user)->get(route('teaching-journals.create'))->assertForbidden();
    $this->actingAs($user)->get(route('teaching-journals.export-pdf'))->assertForbidden();
    $this->actingAs($user)->get(route('curriculum.index'))->assertForbidden();
    $this->actingAs($user)->get(route('mpk.index'))->assertForbidden();
    $this->actingAs($user)->post(route('mpk.audit.store'))->assertForbidden();
    $this->actingAs($user)->get(route('assets.loans.index'))->assertForbidden();
    $this->actingAs($user)->get(route('my-hrd.portal'))->assertForbidden();
    $this->actingAs($user)->get(route('growth.character.assess_form'))->assertForbidden();
    $this->actingAs($user)->post(route('growth.character.award_merit'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.assets.maintenance.scan', 'non-existent-uuid'))->assertForbidden();
})->with(['student', 'siswa', 'parent', 'orang_tua']);

test('proteksi IDOR perpustakaan dan sertifikat menghormati alias siswa dan orang_tua', function () {
    $this->withoutVite();

    $admin = User::factory()->create(['role' => 'admin']);
    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $orangTuaUser = User::factory()->create(['role' => 'orang_tua']);
    $otherStudentUser = User::factory()->create(['role' => 'siswa']);
    $classroom = Classroom::factory()->create();

    $siswa = Student::factory()->create([
        'user_id' => $siswaUser->id,
        'parent_id' => $orangTuaUser->id,
        'classroom_id' => $classroom->id,
    ]);
    $otherStudent = Student::factory()->create([
        'user_id' => $otherStudentUser->id,
        'classroom_id' => $classroom->id,
    ]);

    $this->actingAs($siswaUser)->get(route('library.student-card', $siswa))->assertOk();
    $this->actingAs($siswaUser)->get(route('library.student-card', $otherStudent))->assertForbidden();
    $this->actingAs($orangTuaUser)->get(route('library.student-card', $siswa))->assertOk();
    $this->actingAs($orangTuaUser)->get(route('library.student-card', $otherStudent))->assertForbidden();

    $this->actingAs($siswaUser)->get(route('library.reading-history'))->assertOk();
    $this->actingAs($siswaUser)->get(route('library.recommendations'))->assertOk();
    $this->actingAs($orangTuaUser)->get(route('library.recommendations'))->assertForbidden();

    $template = CertificateTemplate::create([
        'name' => 'Piagam Prestasi',
        'is_active' => true,
        'created_by' => $admin->id,
    ]);
    IssuedCertificate::create([
        'student_id' => $siswaUser->id,
        'template_id' => $template->id,
        'verification_code' => 'CERT-OWN-001',
        'issued_at' => now(),
    ]);
    IssuedCertificate::create([
        'student_id' => $otherStudentUser->id,
        'template_id' => $template->id,
        'verification_code' => 'CERT-OTHER-002',
        'issued_at' => now(),
    ]);

    $this->actingAs($siswaUser)
        ->get(route('certificates.templates'))
        ->assertOk()
        ->assertSee('CERT-OWN-001')
        ->assertDontSee('CERT-OTHER-002');

    $this->actingAs($orangTuaUser)
        ->get(route('certificates.templates'))
        ->assertOk()
        ->assertSee('CERT-OWN-001')
        ->assertDontSee('CERT-OTHER-002');

    $this->actingAs($admin)
        ->get(route('certificates.templates'))
        ->assertOk()
        ->assertSee('CERT-OWN-001')
        ->assertSee('CERT-OTHER-002');
});

test('proteksi IDOR P5, UKS, Tahfidz, dan Konseling berlaku untuk alias siswa dan orang_tua', function () {
    AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);

    $siswaUser = User::factory()->create(['role' => 'siswa']);
    $orangTuaUser = User::factory()->create(['role' => 'orang_tua']);
    $otherStudentUser = User::factory()->create(['role' => 'siswa']);
    $classroom = Classroom::factory()->create();

    $siswa = Student::factory()->create([
        'user_id' => $siswaUser->id,
        'parent_id' => $orangTuaUser->id,
        'classroom_id' => $classroom->id,
    ]);
    $otherStudent = Student::factory()->create([
        'user_id' => $otherStudentUser->id,
        'classroom_id' => $classroom->id,
    ]);

    $this->actingAs($siswaUser)->get(route('growth.character.student_report', $siswaUser->id))->assertOk();
    $this->actingAs($siswaUser)->get(route('growth.character.student_report', $otherStudentUser->id))->assertForbidden();
    $this->actingAs($orangTuaUser)->get(route('growth.character.student_report', $siswaUser->id))->assertOk();
    $this->actingAs($orangTuaUser)->get(route('growth.character.student_report', $otherStudentUser->id))->assertForbidden();

    $this->actingAs($orangTuaUser)->get(route('uks.student-history', $siswa))->assertOk();
    $this->actingAs($orangTuaUser)->get(route('uks.student-history', $otherStudent))->assertForbidden();

    $this->actingAs($siswaUser)->get(route('tahfidz.show', $siswa))->assertOk();
    $this->actingAs($siswaUser)->get(route('tahfidz.show', $otherStudent))->assertForbidden();
    $this->actingAs($orangTuaUser)->get(route('tahfidz.show', $siswa))->assertOk();
    $this->actingAs($orangTuaUser)->get(route('tahfidz.show', $otherStudent))->assertForbidden();

    $this->actingAs($siswaUser)->get(route('counseling.request-form'))->assertOk();
    $this->actingAs($orangTuaUser)->get(route('counseling.request-form'))->assertOk();
});

test('callback pembayaran parent portal mengarahkan ke rute UUID siswa yang valid', function () {
    config(['services.midtrans.simulator' => true]);
    AcademicYear::factory()->create(['is_active' => true]);

    $orangTuaUser = User::factory()->create(['role' => 'orang_tua']);
    $classroom = Classroom::factory()->create();
    $student = Student::factory()->create([
        'parent_id' => $orangTuaUser->id,
        'classroom_id' => $classroom->id,
    ]);
    $category = PaymentCategory::factory()->create(['default_amount' => 500000]);
    $billing = Billing::factory()->create([
        'student_id' => $student->id,
        'payment_category_id' => $category->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
    ]);

    $callback = $this->actingAs($orangTuaUser)->post(route('parent-portal.billings.callback', $billing->id));
    $callback->assertRedirect(route('parent-portal.billings', $student));

    $this->actingAs($orangTuaUser)->get(route('parent-portal.billings', $student))->assertOk();
});

test('superadmin tidak salah diklasifikasikan sebagai student atau parent pada endpoint terguarded', function () {
    $this->withoutVite();
    AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);

    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $student = Student::factory()->create();

    $this->actingAs($superadmin)->get(route('counseling.index'))->assertOk();
    $this->actingAs($superadmin)->get(route('event-attendance.index'))->assertOk();
    $this->actingAs($superadmin)->get(route('library.student-card', $student))->assertOk();
    $this->actingAs($superadmin)->get(route('tahfidz.show', $student))->assertOk();
    $this->actingAs($superadmin)->get(route('uks.student-history', $student))->assertOk();
});
