<?php

use App\Domains\School\Services\SchoolBrandingDomainService;
use App\Models\Setting;
use App\Models\User;
use App\Support\School;
use Symfony\Component\Finder\Finder;

/*
 * The school name used to be typed out in ~290 views. It now comes from the
 * branding settings (Admin > Profil Sekolah) with config/school.php as fallback.
 */

test('without saved settings the names come from config/school.php', function () {
    config([
        'school.name' => 'SMA Contoh 2 Bandung',
        'school.short_name' => 'SMA Contoh 2',
        'school.foundation_name' => 'Yayasan Contoh (YC)',
    ]);

    expect(school_name())->toBe('SMA Contoh 2 Bandung')
        ->and(school_short_name())->toBe('SMA Contoh 2')
        ->and(school_foundation_name())->toBe('Yayasan Contoh (YC)')
        ->and(school_foundation_name(false))->toBe('Yayasan Contoh');
});

test('saved branding settings take precedence over config', function () {
    Setting::set('school_name', 'SMA Nusantara 3 Solo');
    Setting::set('school_short_name', 'SMA Nusantara 3');

    expect(School::name())->toBe('SMA Nusantara 3 Solo')
        ->and(School::shortName())->toBe('SMA Nusantara 3');

    Setting::set('school_short_name', '');
    expect(School::shortName())->toBe(config('school.short_name'));
});

test('pages render the configured name instead of a hardcoded one', function () {
    Setting::set('school_name', 'SMA Nusantara 3 Solo');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('treasury.accounts.index'))
        ->assertOk()
        ->assertSee('SMA Nusantara 3 Solo - Rekening Bank')
        ->assertDontSee('SMA Islam Sultan Agung 1 Semarang');
});

test('the public home page uses the configured name, including its JSON-LD', function () {
    Setting::set('school_name', 'SMA Nusantara 3 Solo');

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('<meta name="author" content="SMA Nusantara 3 Solo">')
        ->toContain('"name": "SMA Nusantara 3 Solo"');
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
    expect(json_decode($m[1], true))->toMatchArray(['@type' => 'School', 'name' => 'SMA Nusantara 3 Solo']);
});

test('the branding form saves the short name used in signatures', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.school.branding.update'), [
        'school_name' => 'SMA Nusantara 3 Solo', 'school_short_name' => 'SMA Nusantara 3',
        'foundation_name' => 'Yayasan Nusantara', 'school_npsn' => '12345678', 'school_akreditasi' => 'A',
        'school_address' => 'Jl. Contoh 1', 'school_phone' => '0271', 'school_email' => 'info@contoh.sch.id',
        'principal_name' => 'Kepala', 'principal_nip' => '1',
        'school_latitude' => '-7.56', 'school_longitude' => '110.82', 'school_geofence_radius' => '200',
    ])->assertRedirect(route('admin.school.branding.index'));

    expect(app(SchoolBrandingDomainService::class)->getFormalHeaderData()['principal_signature_title'])->toBe('Kepala SMA Nusantara 3')
        ->and(school_name())->toBe('SMA Nusantara 3 Solo');
});

test('no view spells out the school or foundation name any more', function () {
    $hardcoded = [];
    $files = Finder::create()->files()->in(resource_path('views'))->name('*.blade.php');
    foreach ($files as $file) {
        foreach (file($file->getRealPath()) as $i => $line) {
            if (preg_match('/SMA Islam Sultan Agung 1|Yayasan Badan Wakaf Sultan Agung/i', $line)) {
                $hardcoded[] = $file->getRelativePathname().':'.($i + 1);
            }
        }
    }

    expect($hardcoded)->toBe([]);
});
