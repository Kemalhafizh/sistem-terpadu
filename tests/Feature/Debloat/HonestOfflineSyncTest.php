<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\DigitalVerification;
use App\Models\OfflineSyncQueue;
use App\Models\Setting;
use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

/*
 * The offline sync queue filled in what the app did not send (Al-Mulk 1-30,
 * grade A, the first teacher in the database, academic year and classroom
 * id 1) and answered "synced" for actions it never wrote. Certificates linked
 * to a hard-coded domain page that does not exist.
 */

beforeEach(function () {
    Cache::forget('academic_year_current');
    $this->year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
    $this->user = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create(['user_id' => $this->user->id, 'classroom_id' => Classroom::factory()->create()->id]);
    $this->teacher = User::factory()->create(['role' => 'guru']);
    Sanctum::actingAs($this->user);
});

function sync(array ...$actions)
{
    return test()->postJson('/api/v1/debloat/sync/queue', ['actions' => $actions])->assertOk();
}

function setoran(array $overrides = []): array
{
    return ['client_uuid' => 'setoran-1', 'action_type' => 'MUTABAAH_LOG', 'payload' => $overrides + [
        'surah_number' => 78, 'surah_name' => "An-Naba'", 'ayah_start' => 1, 'ayah_end' => 16,
        'teacher_id' => test()->teacher->id,
    ]];
}

test('setoran lengkap tersimpan apa adanya, tanpa nilai karangan', function () {
    sync(setoran())->assertJsonPath('data.processed_count', 1);

    expect(TahfidzLog::sole())
        ->surah_number->toBe(78)
        ->from_ayat->toBe(1)->to_ayat->toBe(16)
        ->teacher_id->toBe($this->teacher->id)
        // No grade sent, no grade stored (it used to become "A").
        ->grade->toBeNull();
});

test('setoran yang kurang lengkap gagal dengan pesan, tidak diisi default', function (string $field) {
    $action = setoran();
    unset($action['payload'][$field]);

    sync($action)
        ->assertJsonPath('data.processed_count', 0)
        ->assertJsonPath('data.results.0.status', 'FAILED')
        ->assertJsonPath('data.results.0.error', fn ($m) => str_contains($m, 'Setoran tahfidz belum lengkap') && str_contains($m, $field));

    expect(TahfidzLog::count())->toBe(0);
})->with(['surah_number', 'ayah_start', 'ayah_end', 'teacher_id']);

test('guru penerima setoran harus akun guru sungguhan', function () {
    sync(setoran(['teacher_id' => User::factory()->create(['role' => 'student'])->id]))
        ->assertJsonPath('data.results.0.status', 'FAILED');

    expect(TahfidzLog::count())->toBe(0);
});

test('aksi yang tidak disimpan tidak dilaporkan sukses', function () {
    sync(
        ['client_uuid' => 'jurnal-1', 'action_type' => 'TEACHING_JOURNAL', 'payload' => ['topic' => 'X']],
        ['client_uuid' => 'aneh-1', 'action_type' => 'APA_SAJA', 'payload' => []],
    )
        ->assertJsonPath('data.processed_count', 0)
        ->assertJsonPath('data.results.0.status', 'FAILED')
        ->assertJsonPath('data.results.0.error', 'Jenis aksi TEACHING_JOURNAL belum didukung untuk sinkronisasi.')
        ->assertJsonPath('data.results.1.status', 'FAILED');

    // The message follows the account's language like every other API message.
    $this->user->update(['preferred_locale' => 'en']);
    Sanctum::actingAs($this->user->fresh());
    sync(['client_uuid' => 'jurnal-2', 'action_type' => 'TEACHING_JOURNAL', 'payload' => []])
        ->assertJsonPath('data.results.0.error', 'Action type TEACHING_JOURNAL is not supported for sync yet.');
});

test('akun tanpa data siswa atau tanpa tahun ajaran aktif tidak memakai id 1', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'student']));
    sync(['client_uuid' => 'hadir-x', 'action_type' => 'ATTENDANCE_CHECKIN', 'payload' => ['status' => 'H']])
        ->assertJsonPath('data.results.0.status', 'FAILED')
        ->assertJsonPath('data.results.0.error', 'Data siswa untuk akun ini belum tercatat. Hubungi tata usaha sekolah.');

    Sanctum::actingAs($this->user);
    $this->year->update(['is_active' => false]);
    Cache::forget('academic_year_current');
    sync(['client_uuid' => 'hadir-y', 'action_type' => 'ATTENDANCE_CHECKIN', 'payload' => ['status' => 'H']])
        ->assertJsonPath('data.results.0.status', 'FAILED')
        ->assertJsonPath('data.results.0.error', fn ($m) => str_contains($m, 'tahun ajaran aktif'));

    $this->assertDatabaseCount('attendances', 0);
});

test('aksi yang gagal bisa dikirim ulang dengan client_uuid yang sama', function () {
    $incomplete = setoran();
    unset($incomplete['payload']['teacher_id']);
    sync($incomplete)->assertJsonPath('data.results.0.status', 'FAILED');

    // It used to be stuck: the FAILED row's unique client_uuid blocked any retry.
    sync(setoran())->assertJsonPath('data.results.0.status', 'PROCESSED');

    expect(TahfidzLog::count())->toBe(1)
        ->and(OfflineSyncQueue::where('client_uuid', 'setoran-1')->sole()->status)->toBe('PROCESSED');
    sync(setoran())->assertJsonPath('data.results.0.message', 'Action already processed previously (Idempotent).');
    expect(TahfidzLog::count())->toBe(1);
});

test('tautan verifikasi sertifikat menunjuk ke aplikasi ini dan benar-benar bisa dibuka', function () {
    config(['app.url' => 'https://sekolah-contoh.test']);
    Setting::set('principal_name', 'Kepala Contoh, S.Pd.');

    $this->postJson('/api/v1/debloat/certificates/issue', ['student_id' => $this->student->id, 'document_type' => 'IJAZAH', 'document_number' => 'IJZ-77'])
        ->assertCreated();

    $url = DigitalVerification::sole()->qr_verification_url;
    expect($url)->toBe('https://sekolah-contoh.test/api/v1/certificates/verify/IJZ-77')
        ->not->toContain('sultanagung1');

    $this->getJson(parse_url($url, PHP_URL_PATH))->assertOk()->assertJsonPath('data.document_number', 'IJZ-77');
});
