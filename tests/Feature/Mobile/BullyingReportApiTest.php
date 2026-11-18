<?php

use App\Models\BullyingReport;
use App\Models\User;
use App\Support\Access\MobileCapabilities;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

/*
 * "Lapor Perundungan" in the app (Android FASE 78). Anyone signed in
 * may file a report; only counselling, student affairs and leadership read them.
 */

function bullyingApiUser(string $role): User
{
    $user = User::factory()->create(['role' => $role, 'must_change_password' => false]);
    Sanctum::actingAs($user);

    return $user;
}

function filedReport(array $attributes = []): BullyingReport
{
    return BullyingReport::create([
        'ticket_number' => (string) Str::uuid(), 'description' => 'Diejek di kantin', 'status' => 'reported', ...$attributes,
    ]);
}

test('siapa pun yang login boleh melapor, anonim secara bawaan', function (string $role) {
    bullyingApiUser($role);

    $response = $this->postJson('/api/v1/counseling/bullying-reports', [
        'description' => 'Dipalak kakak kelas di parkiran', 'incident_date' => now()->subDay()->toDateString(), 'location' => 'Parkiran motor',
    ])->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Laporan perundungan terkirim. Guru BK akan menindaklanjutinya.')
        ->assertJsonPath('data.status_code', 'reported')
        ->assertJsonPath('data.status', 'Dilaporkan')
        ->assertJsonPath('data.is_anonymous', true)
        ->assertJsonPath('data.location', 'Parkiran motor');

    // The reporter does not judge type or severity, and the server does not invent them.
    expect($response->json('data'))->type->toBeNull()->type_code->toBeNull()
        ->not->toHaveKey('severity')->not->toHaveKey('reporter')
        ->and($response->json('data.created_at'))->toMatch('/^\d{4}-\d{2}-\d{2}T/');

    expect(BullyingReport::sole())->reporter_id->toBeNull()->severity->toBeNull()
        ->ticket_number->toBe($response->json('data.ticket_number'));
})->with(['siswa', 'student', 'orang_tua', 'guru']);

test('laporan yang tidak anonim menyimpan pelapornya', function () {
    $student = bullyingApiUser('siswa');

    $this->postJson('/api/v1/counseling/bullying-reports', [
        'description' => 'Dipukul saat istirahat', 'type' => 'fisik', 'is_anonymous' => false,
    ])->assertCreated()->assertJsonPath('data.type_code', 'fisik')->assertJsonPath('data.type', 'Fisik');

    expect(BullyingReport::sole()->reporter_id)->toBe($student->id);
});

test('laporan yang tidak lengkap atau tanggalnya di masa depan ditolak', function (array $payload) {
    bullyingApiUser('siswa');

    $this->postJson('/api/v1/counseling/bullying-reports', $payload)->assertUnprocessable();
    expect(BullyingReport::count())->toBe(0);
})->with([
    'tanpa isi' => [['location' => 'Kelas']],
    // A closure, so "tomorrow" is read in the app's timezone once it has booted.
    'tanggal besok' => [fn () => ['description' => 'x', 'incident_date' => now()->addDay()->toDateString()]],
    'jenis tak dikenal' => [['description' => 'x', 'type' => 'bom']],
]);

test('tanpa login tidak bisa melapor', function () {
    $this->postJson('/api/v1/counseling/bullying-reports', ['description' => 'x'])->assertUnauthorized();
});

test('siswa, orang tua, dan guru tidak bisa membaca laporan', function (string $role) {
    $report = filedReport();
    bullyingApiUser($role);

    $this->getJson('/api/v1/counseling/bullying-reports')->assertForbidden();
    $this->getJson("/api/v1/counseling/bullying-reports/{$report->id}")->assertForbidden();
})->with(['siswa', 'orang_tua', 'guru', 'wali_kelas']);

test('BK dan pimpinan membaca laporan tanpa membuka identitas pelapor anonim', function (string $role) {
    $named = User::factory()->create(['role' => 'siswa', 'name' => 'Budi']);
    filedReport(['is_anonymous' => true, 'reporter_id' => null]);
    $open = filedReport(['is_anonymous' => false, 'reporter_id' => $named->id, 'type' => 'verbal', 'severity' => 'sedang']);
    bullyingApiUser($role);

    $list = $this->getJson('/api/v1/counseling/bullying-reports')->assertOk()->json('data');
    expect($list)->toHaveCount(2)
        ->and(collect($list)->firstWhere('id', $open->id))->reporter->toBe('Budi')->severity_code->toBe('sedang')->severity->toBe('Sedang')
        ->and(collect($list)->firstWhere('id', '!=', $open->id))->reporter->toBeNull()->severity->toBeNull();

    $this->getJson("/api/v1/counseling/bullying-reports/{$open->id}")->assertOk()->assertJsonPath('data.reporter', 'Budi');
    $this->getJson('/api/v1/counseling/bullying-reports/999999')->assertNotFound()->assertJsonPath('message', 'Laporan tidak ditemukan.');
})->with(['bk', 'waka_kesiswaan', 'kepala_sekolah']);

test('daftar laporan bisa disaring per status', function () {
    filedReport();
    filedReport(['status' => 'closed']);
    bullyingApiUser('bk');

    expect($this->getJson('/api/v1/counseling/bullying-reports?status=closed')->assertOk()->json('data'))
        ->toHaveCount(1)->{0}->status_code->toBe('closed');
    $this->getJson('/api/v1/counseling/bullying-reports?status=hilang')->assertUnprocessable();
});

test('label mengikuti bahasa pengguna, kodenya tetap', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'siswa', 'must_change_password' => false, 'preferred_locale' => 'en']));

    $this->postJson('/api/v1/counseling/bullying-reports', ['description' => 'Called names', 'type' => 'verbal'])
        ->assertCreated()
        ->assertJsonPath('message', 'Bullying report sent. The counsellor will follow it up.')
        ->assertJsonPath('data.status', 'Reported')->assertJsonPath('data.status_code', 'reported')
        ->assertJsonPath('data.type', 'Verbal');
});

test('menu aplikasi: semua boleh melapor, hanya BK dan pimpinan yang melihat daftar', function () {
    $keys = fn (string $role) => array_column(MobileCapabilities::for(User::factory()->create(['role' => $role])), 'key');

    expect($keys('siswa'))->toContain('counseling.bullying_report')->not->toContain('counseling.bullying_reports')
        ->and($keys('orang_tua'))->toContain('counseling.bullying_report')->not->toContain('counseling.bullying_reports')
        ->and($keys('bk'))->toContain('counseling.bullying_report', 'counseling.bullying_reports');
});
