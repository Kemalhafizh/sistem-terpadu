<?php

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\FingerprintImport;
use App\Models\User;
use App\Services\HRD\EmployeeAttendanceRecap;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*
 * FASE 119.6.1: staff sign in on the fingerprint machine and TU imports its
 * log. Importing is idempotent, unknown machine IDs are reported instead of
 * silently dropped, and a month with no data recaps as null, not 0.
 */

function fingerprintEmployee(string $machineId, string $name): Employee
{
    $user = User::factory()->create(['role' => 'guru', 'name' => $name]);

    return Employee::create(['user_id' => $user->id, 'employee_number' => 'NIY.'.$user->id, 'fingerprint_id' => $machineId, 'position' => 'Guru', 'employment_status' => 'gty']);
}

function machineLog(string $name, string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $content);
}

function importLog(object $test, UploadedFile $file, string $role = 'staf_tu')
{
    return $test->actingAs(User::factory()->create(['role' => $role]))->post(route('hrd.fingerprint-import.store'), ['file' => $file]);
}

beforeEach(function () {
    config(['fingerprint.late_after' => '07:00:00', 'fingerprint.delimiter' => 'auto', 'fingerprint.has_header' => true, 'fingerprint.datetime_format' => null]);
    $this->ahmad = fingerprintEmployee('12', 'Ahmad');
    $this->siti = fingerprintEmployee('15', 'Siti');
    $this->zkteco = "AC-No.,Name,Time,State\n"
        ."12,Ahmad,2026-10-05 06:51:10,C/In\n"
        ."12,Ahmad,2026-10-05 14:02:44,C/Out\n"
        ."15,Siti,2026-10-05 07:20:03,C/In\n"
        ."15,Siti,2026-10-05 15:10:00,C/Out\n"
        ."12,Ahmad,2026-10-06 06:45:00,C/In\n";
});

test('scan pertama jadi jam masuk, scan terakhir jam pulang, terlambat setelah jam masuk', function () {
    importLog($this, machineLog('attlog.csv', $this->zkteco))->assertRedirect(route('hrd.fingerprint-import.index'));

    $ahmad = EmployeeAttendance::where('employee_id', $this->ahmad->id)->orderBy('date')->get();
    expect($ahmad)->toHaveCount(2)
        ->and($ahmad[0])->check_in_time->toBe('06:51:10')->check_out_time->toBe('14:02:44')->status->toBe('hadir')->source->toBe('fingerprint')
        // A single scan is an arrival with no recorded departure.
        ->and($ahmad[1])->check_in_time->toBe('06:45:00')->check_out_time->toBeNull();
    expect(EmployeeAttendance::where('employee_id', $this->siti->id)->sole()->status)->toBe('terlambat');

    expect(FingerprintImport::sole())->rows_read->toBe(5)->attendances_created->toBe(3)->unknown_ids->toBeNull()
        ->first_date->toDateString()->toBe('2026-10-05')->last_date->toDateString()->toBe('2026-10-06');
});

test('impor file yang sama dua kali tidak menggandakan presensi', function () {
    importLog($this, machineLog('attlog.csv', $this->zkteco));
    importLog($this, machineLog('attlog.csv', $this->zkteco));

    expect(EmployeeAttendance::count())->toBe(3)
        ->and(FingerprintImport::latest('id')->first())->attendances_created->toBe(0)->attendances_updated->toBe(0)->attendances_unchanged->toBe(3);
});

test('log berikutnya yang tumpang tindih melengkapi jam pulang', function () {
    importLog($this, machineLog('pagi.csv', "AC-No.,Time\n12,2026-10-06 06:45:00\n"));
    importLog($this, machineLog('sore.csv', "AC-No.,Time\n12,2026-10-06 06:45:00\n12,2026-10-06 15:30:00\n"));

    expect(EmployeeAttendance::sole())->check_in_time->toBe('06:45:00')->check_out_time->toBe('15:30:00')
        ->and(FingerprintImport::latest('id')->first()->attendances_updated)->toBe(1);
});

test('ID mesin yang tidak dikenal dilaporkan, bukan diabaikan diam-diam', function () {
    importLog($this, machineLog('attlog.csv', $this->zkteco."99,Tamu,2026-10-05 08:00:00,C/In\n99,Tamu,2026-10-05 09:00:00,C/Out\n"))
        ->assertRedirect();

    expect(FingerprintImport::sole()->unknown_ids)->toBe(['99' => 2])
        ->and(EmployeeAttendance::count())->toBe(3);

    $this->get(route('hrd.fingerprint-import.index'))->assertOk()
        ->assertSee('ID 99 (2 scan)')
        ->assertSee('Isi "ID di Mesin Sidik Jari" pada data pegawainya');
});

test('baris yang tidak terbaca dilaporkan dengan nomor barisnya', function () {
    importLog($this, machineLog('attlog.csv', "AC-No.,Time\n12,2026-10-05 06:51:10\n12,kemarin pagi\n,2026-10-05 07:00:00\n"));

    expect(FingerprintImport::sole())->invalid_rows->toBe([
        ['line' => 3, 'reason' => 'Tanggal/jam tidak terbaca'],
        ['line' => 4, 'reason' => 'ID mesin kosong'],
    ])->attendances_created->toBe(1);
});

test('hari yang sudah tercatat cuti atau sakit tidak ditimpa', function () {
    EmployeeAttendance::create(['employee_id' => $this->ahmad->id, 'date' => '2026-10-05', 'status' => 'sakit']);

    importLog($this, machineLog('attlog.csv', $this->zkteco));

    expect(EmployeeAttendance::where('employee_id', $this->ahmad->id)->whereDate('date', '2026-10-05')->sole())
        ->status->toBe('sakit')->check_in_time->toBeNull()
        ->and(FingerprintImport::sole()->leave_days_kept)->toBe(1);
});

test('format mesin lain: titik koma, tanggal dan jam terpisah, tanggal d/m/Y', function () {
    importLog($this, machineLog('fingerspot.csv', "PIN;Nama;Tanggal;Jam\n15;Siti;05/10/2026;06:55\n15;Siti;05/10/2026;14:00\n"));

    expect(EmployeeAttendance::where('employee_id', $this->siti->id)->sole())
        ->check_in_time->toBe('06:55:00')->check_out_time->toBe('14:00:00')->status->toBe('hadir');
});

test('log dari file Excel', function () {
    $sheet = new Spreadsheet;
    $sheet->getActiveSheet()->fromArray([['AC-No.', 'Time'], ['12', '2026-10-05 06:51:10'], ['12', '2026-10-05 14:02:44']]);
    $path = tempnam(sys_get_temp_dir(), 'fp').'.xlsx';
    (new Xlsx($sheet))->save($path);

    importLog($this, new UploadedFile($path, 'attlog.xlsx', null, null, true));

    expect(EmployeeAttendance::sole())->check_in_time->toBe('06:51:10')->check_out_time->toBe('14:02:44');
});

test('file tanpa kolom ID mesin ditolak dengan pesan, tanpa menyimpan apa pun', function () {
    importLog($this, machineLog('salah.csv', "Nama,Waktu\nAhmad,2026-10-05 06:51:10\n"))
        ->assertSessionHasErrors(['file' => 'Kolom ID mesin tidak ditemukan. Sesuaikan nama kolomnya di config/fingerprint.php.']);

    expect(EmployeeAttendance::count())->toBe(0)->and(FingerprintImport::count())->toBe(0);
});

test('hanya TU, kepala sekolah, dan admin yang boleh mengimpor', function (string $role) {
    importLog($this, machineLog('attlog.csv', $this->zkteco), $role)->assertForbidden();
    $this->get(route('hrd.fingerprint-import.index'))->assertForbidden();

    expect(EmployeeAttendance::count())->toBe(0);
})->with(['guru', 'bk', 'siswa', 'orang_tua']);

test('rekap bulan tanpa data bernilai null, bukan 0', function () {
    $recap = app(EmployeeAttendanceRecap::class)->month(2026, 9);

    expect($recap['working_days'])->toBeNull()
        ->and($recap['rows'])->toHaveCount(2)
        ->and($recap['rows'][0])->hadir->toBeNull()->terlambat->toBeNull()->tidak_hadir->toBeNull()->rate->toBeNull();

    $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('hrd.attendance-report', ['month' => '09', 'year' => 2026]))
        ->assertOk()->assertSee('Belum ada data presensi untuk bulan ini.')->assertDontSee('0%');
});

test('rekap bulanan dari log: hadir, terlambat, dan tidak hadir per hari kerja yang tercatat', function () {
    importLog($this, machineLog('attlog.csv', $this->zkteco));

    $recap = app(EmployeeAttendanceRecap::class);
    // The log covers two days (5 and 6 October).
    expect($recap->forEmployee($this->ahmad, 2026, 10))->toMatchArray(['working_days' => 2, 'hadir' => 2, 'terlambat' => 0, 'tidak_hadir' => 0, 'rate' => 100.0])
        ->and($recap->forEmployee($this->siti, 2026, 10))->toMatchArray(['working_days' => 2, 'hadir' => 0, 'terlambat' => 1, 'tidak_hadir' => 1, 'rate' => 50.0]);
});

test('check-in GPS dan selfie pegawai sudah tidak ada', function () {
    expect(Route::has('my-hrd.attendance'))->toBeFalse();
    $this->actingAs($this->ahmad->user)->get(route('my-hrd.portal'))->assertOk()
        ->assertSee('Presensi pegawai dicatat mesin sidik jari')->assertDontSee('CHECK IN SEKARANG');
});

test('admin tanpa data pegawai tidak lagi dibuatkan data dengan gaji karangan', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('my-hrd.portal'))->assertOk();

    expect(Employee::count())->toBe(2);
});
