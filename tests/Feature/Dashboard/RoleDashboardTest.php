<?php

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\MutabaahItem;
use App\Models\Student;
use App\Models\User;

/*
 * One dashboard per role, whatever the spelling of the
 * role, and no made-up figures when there is no data.
 */

test('setiap ejaan peran mendapat dashboard perannya', function (string $role, string $dashboard, string $viewKey) {
    $user = User::factory()->create(['role' => $role]);
    if ($dashboard === 'student') {
        Student::factory()->create(['user_id' => $user->id]);
    }

    expect($user->dashboardRole())->toBe($dashboard);
    $this->actingAs($user)->get('/dashboard')->assertOk()->assertViewHas($viewKey);
})->with([
    ['siswa', 'student', 'studentSchedulesToday'],
    ['student', 'student', 'studentSchedulesToday'],
    ['orang_tua', 'parent', 'children'],
    ['parent', 'parent', 'children'],
    ['guru', 'teacher', 'schedules'],
    ['bk', 'teacher', 'schedules'],
    ['kepala_sekolah', 'leadership', 'todayAttendanceRate'],
    ['staf_tu', 'leadership', 'todayAttendanceRate'],
    ['waka_sarpras', 'leadership', 'todayAttendanceRate'],
]);

test('staf tanpa dashboard sendiri tidak melihat angka sekolah', function () {
    $uks = User::factory()->create(['role' => 'petugas_uks']);

    expect($uks->dashboardRole())->toBe('general');
    $this->actingAs($uks)->get('/dashboard')->assertOk()
        ->assertViewMissing('todayAttendanceRate')
        ->assertViewMissing('totalBillings');
});

test('dashboard pimpinan tanpa data menampilkan kosong, bukan angka karangan', function () {
    MutabaahItem::query()->update(['is_active' => false]);

    $response = $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']))->get('/dashboard')->assertOk();

    // No attendance, no bills, no active mutaba'ah items: nothing to report.
    $response->assertViewHas('todayAttendanceRate', null)
        ->assertViewHas('currentMonthName', null)
        ->assertViewHas('totalBillings', null)
        ->assertViewHas('collectionRate', null)
        ->assertViewHas('mutabaahAverageScore', null)
        ->assertViewHas('mutabaahGradeAPercent', null);
    expect($response->getContent())->not->toContain('75.4%')->not->toContain('32.5%');
});

test('tingkat kehadiran dihitung dari presensi yang ada', function () {
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $recorder = User::factory()->create(['role' => 'guru']);
    foreach (['H', 'H', 'S', 'A'] as $status) {
        $student = Student::factory()->create();
        Attendance::create([
            'student_id' => $student->id, 'academic_year_id' => $year->id, 'classroom_id' => $student->classroom_id,
            'date' => now()->toDateString(), 'status' => $status, 'recorded_by' => $recorder->id,
        ]);
    }

    $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/dashboard')->assertOk()
        ->assertViewHas('todayAttendanceRate', 50.0);
});

test('tanpa amalan aktif, skor mutabaah anak kosong, bukan D', function () {
    MutabaahItem::query()->update(['is_active' => false]);
    $parent = User::factory()->create(['role' => 'orang_tua']);
    Student::factory()->create(['parent_id' => $parent->id]);

    $children = $this->actingAs($parent)->get('/dashboard')->assertOk()->viewData('children');

    expect($children[0]['childMutabaahScore'])->toBeNull()
        ->and($children[0]['childMutabaahGrade'])->toBeNull();
});

test('guru tanpa persetujuan tertunda tidak melihat klaim "semua modul aman"', function () {
    $html = $this->actingAs(User::factory()->create(['role' => 'guru']))->get('/dashboard')->assertOk()->getContent();

    expect($html)->not->toContain('Semua modul terpantau aman')
        ->toContain('Tidak ada persetujuan yang menunggu Anda.');
});

test('kesehatan keuangan tampil untuk yang boleh melihat posisi kas, kosong bila belum ada transaksi', function () {
    $response = $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']))->get('/dashboard')->assertOk();

    $health = $response->viewData('financialHealth');
    expect($health->cash_reserve)->toBeNull()
        ->and($health->monthly_burn_rate)->toBeNull()
        ->and($response->getContent())->toContain('Kesehatan Keuangan');

    // waka_sarpras has a leadership dashboard but may not open the treasury.
    $this->actingAs(User::factory()->create(['role' => 'waka_sarpras']))->get('/dashboard')->assertOk()
        ->assertViewMissing('financialHealth');
});
