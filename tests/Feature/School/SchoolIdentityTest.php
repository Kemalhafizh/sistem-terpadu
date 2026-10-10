<?php

use App\Domains\School\Services\SchoolBrandingDomainService;
use App\Models\Setting;
use App\Models\User;
use App\Support\School;
use Symfony\Component\Finder\Finder;

/*
 * The school's identity used to be scattered: official documents printed
 * eight phone numbers, seven e-mail addresses and three street addresses, an
 * invented NPSN (20328888) and invented officials ("Drs. H. Ahmad Dahlan,
 * M.Pd." and others) with made-up NIPs; GPS check-in paths used four campus
 * centres up to ~4.8 km apart. Everything now reads App\Support\School.
 */

function runSchoolCleanupMigration(): void
{
    (require database_path('migrations/2026_12_20_000004_remove_placeholder_school_settings.php'))->up();
}

// ── Identity ───────────────────────────────────────────────────────────────

test('tanpa pengaturan, identitas memakai data resmi terverifikasi dan data pejabat kosong', function () {
    expect(School::npsn())->toBe('20328918')
        ->and(School::phone())->toBe('(024) 8312631')
        ->and(School::email())->toBe('opsmaissula1smg@gmail.com')
        ->and(School::websiteLabel())->toBe('smaislamsultanagung1smg.sch.id')
        ->and(School::whatsapp())->toBe('+6289682585200')
        // No default for people or for accreditation.
        ->and(School::principalName())->toBeNull()
        ->and(School::principalNip())->toBeNull()
        ->and(School::accreditation())->toBeNull()
        ->and(School::missingOfficialFields())->toBe([
            'school_akreditasi' => 'Akreditasi',
            'principal_name' => 'Nama Kepala Sekolah',
            'principal_nip' => 'NIP Kepala Sekolah',
        ]);
});

test('nilai yang disimpan admin mengalahkan config, dan nilai kosong kembali ke config', function () {
    Setting::set('school_phone', '(024) 1111111');
    Setting::set('principal_name', 'Kepala Nyata, M.Pd.');
    Setting::set('school_email', '');

    expect(School::phone())->toBe('(024) 1111111')
        ->and(School::principalName())->toBe('Kepala Nyata, M.Pd.')
        ->and(School::email())->toBe(config('school.email'));
});

test('baris kontak hanya memuat bagian yang diketahui', function () {
    Setting::set('school_phone', '(024) 1111111');
    config(['school.email' => null]);

    expect(School::contactLine())->toBe(config('school.address').' | Telp: (024) 1111111')
        ->and(school_contact_line(['phone', 'website'], ' • '))->toBe('Telp: (024) 1111111 • Website: smaislamsultanagung1smg.sch.id');
});

test('kop surat tidak mencetak NPSN/akreditasi/kepala sekolah palsu saat datanya kosong', function () {
    $header = app(SchoolBrandingDomainService::class)->getFormalHeaderData();

    expect($header['line_3'])->toBe('NPSN: 20328918') // accreditation unknown → left out
        ->and($header['principal_name'])->toBeNull()
        ->and($header['principal_nip'])->toBeNull()
        ->and(signature_name(school_principal_name()))->toBe('( ........................................ )')
        ->and(signature_nip(school_principal_nip()))->toBe('NIP. ........................................');
});

test('halaman Profil Sekolah memperingatkan data resmi yang belum diisi', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.school.branding.index'))
        ->assertOk()
        ->assertSee('Data resmi belum lengkap')
        ->assertSee('NIP Kepala Sekolah')
        ->assertDontSee('Ahmad Dahlan')
        ->assertDontSee('20328888');
});

test('NPSN harus 8 digit', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->from(route('admin.school.branding.index'))->post(route('admin.school.branding.update'), [
        'school_name' => 'X', 'foundation_name' => 'Y', 'school_npsn' => '123', 'school_akreditasi' => 'A',
        'school_address' => 'Z', 'school_phone' => '1', 'school_email' => 'a@b.id',
        'principal_name' => 'K', 'principal_nip' => '1',
        'school_latitude' => '-7', 'school_longitude' => '110', 'school_geofence_radius' => '200',
    ])->assertSessionHasErrors('school_npsn');
});

// ── Clean-up of planted settings ────────────────────────────────────────────

test('migrasi pembersih hanya menghapus nilai titipan, bukan isian admin', function () {
    foreach ([
        'school_npsn' => '20328888',
        'principal_name' => 'Drs. H. Ahmad Dahlan, M.Pd.',
        'principal_nip' => '196807151994031005',
        'school_phone' => '021-1234567',
        'school_latitude' => '-6.9535',
        'school_longitude' => '110.4355',
        'school_attendance_radius' => '100',
        'active_academic_year' => '2026/2027 Semester Ganjil',
        // Real values an admin entered — must survive.
        'school_email' => 'tu@sekolah-nyata.sch.id',
        'school_geofence_radius' => '180',
    ] as $key => $value) {
        Setting::set($key, $value);
    }

    runSchoolCleanupMigration();

    foreach (['school_npsn', 'principal_name', 'principal_nip', 'school_phone', 'school_latitude', 'school_longitude', 'school_attendance_radius', 'active_academic_year'] as $key) {
        expect(Setting::where('key', $key)->exists())->toBeFalse("{$key} should have been removed");
    }
    expect(School::email())->toBe('tu@sekolah-nyata.sch.id')
        ->and(School::campus()['radius_meters'])->toBe(180.0)
        ->and(School::npsn())->toBe('20328918')
        ->and(School::principalName())->toBeNull();
});

// ── One geofence for every check-in path ───────────────────────────────────

test('mobile/config mengirim titik kampus dan kontak resmi sekolah', function () {
    $response = $this->getJson('/api/v1/mobile/config?platform=android&build=105')
        ->assertOk()
        ->assertJsonPath('contact_support.whatsapp', '+6289682585200')
        ->assertJsonPath('contact_support.email', 'opsmaissula1smg@gmail.com');
    // JSON turns 250.0 into 250, so compare by value.
    expect($response->json('campus'))->toEqual(School::campus());
});

// ── Guard ───────────────────────────────────────────────────────────────────

test('tidak ada kontak, NPSN, atau pejabat karangan yang ditulis lagi di view atau app', function () {
    $planted = [
        '20328888', 'Ahmad Dahlan', '196807151994031005',
        '8314941', '8313922', '6580824', '6580019', '8316532', '8313364', '8313527', '8312137', '6712345', '(024) 123456', '021-1234567',
        'info@sultanagung1.sch.id', 'smaisla1smg', 'smaislam-sa1', 'smaisa1_smg', 'smaislam-sula1', 'smaislasa1', 'smaislam1-smg', 'info@sistem-terpadu',
        'Sukarno-Hatta No.4', 'Jalan Pahlawan No. 1', '+6281234567890',
        'Soetarto', 'Mulyono', 'Anis Wahdi', 'M. Said, M.Pd', 'H. Soleh', 'Soetrisno', 'Wahyu Ningsih', 'Tjoek Subhan', 'Hj. Siti Aminah, S.E.',
    ];
    $found = [];
    $files = Finder::create()->files()->in([resource_path('views'), app_path()])->name('*.php');
    foreach ($files as $file) {
        $contents = file_get_contents($file->getRealPath());
        foreach ($planted as $needle) {
            // Comments documenting what was removed are fine; only code/markup counts.
            $code = preg_replace('#/\*.*?\*/|^\s*(//|\*).*$#ms', '', $contents);
            if (str_contains($code, $needle)) {
                $found[] = $file->getRelativePathname().' → '.$needle;
            }
        }
    }

    expect($found)->toBe([]);
});
