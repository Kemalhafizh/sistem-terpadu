<?php

use App\Models\DigitalVerification;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\TotpSecurityService;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\Finder\Finder;

/*
 * The school's name comes from the school settings (school_name(),
 * school_short_name(); config/school.php as the fallback), so another school
 * can run the app by changing settings. About forty places wrote it out by
 * hand, and certificates were signed by a made-up "Drs. H. Sukarno, M.Pd".
 */

beforeEach(function () {
    config(['school.name' => 'SMA Contoh Nusantara Kota Baru', 'school.short_name' => 'SMA Contoh Nusantara']);
});

test('nama sekolah tidak ditulis langsung di app atau view', function () {
    $needles = ['SMA Islam Sultan Agung', 'Sultan Agung 1', 'Badan Wakaf Sultan Agung', 'Sukarno, M.Pd'];
    $found = [];
    // resources/views/scribe is generated (php artisan scribe:generate) from config/scribe.php.
    $files = Finder::create()->files()->in([resource_path('views'), app_path()])->name('*.php')->notPath('scribe');
    foreach ($files as $file) {
        // Comments documenting what was removed are fine; only code/markup counts.
        $code = preg_replace('#/\*.*?\*/|^\s*(//|\*).*$#ms', '', file_get_contents($file->getRealPath()));
        foreach ($needles as $needle) {
            if (str_contains($code, $needle)) {
                $found[] = $file->getRelativePathname().' → '.$needle;
            }
        }
    }

    expect($found)->toBe([]);
});

test('API dan halaman memakai nama sekolah dari pengaturan', function () {
    $this->getJson('/api/v1/mobile/config')->assertOk()->assertJsonFragment(['app_name' => 'SISTA SMA Contoh Nusantara']);

    Sanctum::actingAs(User::factory()->create(['role' => 'student']));
    $this->getJson('/api/v1/gateway/student/me')->assertOk()->assertJsonPath('data.school', 'SMA Contoh Nusantara Kota Baru');

    expect(app(TotpSecurityService::class)->generateQrCodeUrl('a@b.c', 'SECRET'))->toContain('issuer=SMA%20Contoh%20Nusantara&');

    // Translation strings use :school / :school_short; a call site that forgets
    // school_placeholders() would print the placeholder itself.
    foreach ([route('spmb.welcome'), '/'] as $page) {
        $this->get($page)->assertOk()->assertSee('SMA Contoh Nusantara')
            ->assertDontSee('Sultan Agung 1')->assertDontSee(':school', false);
    }
    $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']))->get(route('dashboard'))
        ->assertOk()->assertSee('SMA Contoh Nusantara')->assertDontSee(':school', false);
});

test('sertifikat ditandatangani kepala sekolah dari pengaturan, bukan nama karangan', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id, 'nisn' => '0012345678']);
    Sanctum::actingAs($studentUser);
    $issue = fn () => $this->postJson('/api/v1/debloat/certificates/issue', [
        'student_id' => $student->id, 'document_type' => 'IJAZAH', 'document_number' => 'IJZ-1',
    ]);

    // No principal in the settings yet: refuse instead of signing as someone made up.
    $issue()->assertStatus(422)->assertJsonPath('success', false)
        ->assertJsonPath('message', fn ($m) => str_contains($m, 'Nama kepala sekolah belum diisi'));
    expect(DigitalVerification::count())->toBe(0);

    Setting::set('principal_name', 'Kepala Contoh, S.Pd.');
    $issue()->assertCreated();

    expect(DigitalVerification::sole())
        ->issuer_name->toBe('Kepala Contoh, S.Pd.')
        ->issuer_title->toBe('Kepala SMA Contoh Nusantara Kota Baru')
        ->metadata->institution->toBe('SMA Contoh Nusantara Kota Baru');
});
