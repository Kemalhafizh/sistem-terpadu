<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;

/*
 * (119.2, 119.4): the sidebar has one menu per area and no
 * "Enterprise" sections. Counselling, bullying reports and inclusive
 * education sit under Bimbingan Konseling; bills, bank accounts, petty cash
 * and scholarships under Keuangan.
 */

function sidebarFor(object $test, string $role): string
{
    $user = User::factory()->create(['role' => $role]);
    if (in_array($role, ['siswa', 'student'], true)) {
        Student::factory()->create(['user_id' => $user->id]);
    }

    $page = $test->actingAs($user)->get('/dashboard')->assertOk()->getContent();

    // Only the sidebar: the dashboard body has quick links of its own.
    return Str::between($page, 'id="sidebar-nav"', '</nav>');
}

function hrefCount(string $html, string $routeName): int
{
    return substr_count($html, 'href="'.route($routeName).'"');
}

test('tidak ada lagi seksi Enterprise di sidebar', function (string $role) {
    expect(sidebarFor($this, $role))
        ->not->toContain('Enterprise');
})->with(['admin', 'kepala_sekolah', 'bk', 'guru']);

test('setiap menu muncul sekali', function (string $role, array $routes) {
    $html = sidebarFor($this, $role);

    foreach ($routes as $routeName) {
        expect(hrefCount($html, $routeName))->toBe(1, "$routeName for $role");
    }
})->with([
    ['admin', ['announcements.index', 'assets.index', 'iep.index', 'tahsin.index', 'finance.billings.index', 'admin.school.branding.index']],
    ['kepala_sekolah', ['announcements.index', 'crisis.bullying.index', 'treasury.accounts.index']],
    ['waka_humas', ['announcements.index']],
]);

test('BK melihat satu menu Bimbingan Konseling', function () {
    $html = sidebarFor($this, 'bk');

    expect($html)->toContain(__('navigation.header_counseling'))
        ->and(hrefCount($html, 'counseling.dashboard'))->toBe(1)
        ->and(hrefCount($html, 'points.index'))->toBe(1)
        ->and(hrefCount($html, 'crisis.bullying.index'))->toBe(1)
        ->and(hrefCount($html, 'iep.index'))->toBe(1)
        ->and($html)->not->toContain(__('navigation.header_finance'));
});

test('TU melihat satu menu Keuangan', function () {
    $html = sidebarFor($this, 'staf_tu');

    expect($html)->toContain(__('navigation.header_finance'));
    foreach (['finance.billings.index', 'treasury.accounts.index', 'treasury.reconciliation.index', 'treasury.petty-cash.index', 'treasury.vendor.index', 'financial.scholarships.programs', 'finance.reports.executive'] as $routeName) {
        expect(hrefCount($html, $routeName))->toBe(1, $routeName);
    }
    expect(hrefCount($html, 'crisis.bullying.index'))->toBe(0);
});

test('siswa hanya melihat tagihannya sendiri di Keuangan, apa pun ejaan perannya', function (string $role) {
    $html = sidebarFor($this, $role);

    expect(hrefCount($html, 'finance.student.index'))->toBe(1)
        ->and(hrefCount($html, 'financial.scholarships.programs'))->toBe(0)
        ->and(hrefCount($html, 'treasury.accounts.index'))->toBe(0)
        ->and(hrefCount($html, 'counseling.dashboard'))->toBe(0);
})->with(['siswa', 'student']);

test('akun siswa dengan ejaan siswa bisa membuka tagihannya', function () {
    $student = Student::factory()->create();
    $student->user->update(['role' => 'siswa']);

    $this->actingAs($student->user)->get(route('finance.student.index'))->assertOk();
    $this->actingAs(User::factory()->create(['role' => 'orang_tua']))->get(route('finance.student.index'))->assertForbidden();
});

test('OSIS dan MPK satu menu', function () {
    $html = sidebarFor($this, 'waka_kesiswaan');

    expect($html)->toContain(e(__('navigation.osis_mpk')))
        ->not->toContain('Lembaga MPK')->not->toContain('Keuangan OSIS')
        ->and(hrefCount($html, 'osis.dashboard'))->toBe(1);
});

test('identitas dan tampilan sekolah satu menu dengan dua tab', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    expect(hrefCount(sidebarFor($this, 'admin'), 'admin.appearance.index'))->toBe(0);
    foreach (['admin.school.branding.index', 'admin.appearance.index'] as $page) {
        $html = $this->actingAs($admin)->get(route($page))->assertOk()->getContent();
        expect($html)->toContain('href="'.route('admin.school.branding.index').'"')
            ->toContain('href="'.route('admin.appearance.index').'"');
    }
});
