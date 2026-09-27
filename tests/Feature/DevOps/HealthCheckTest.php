<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/*
 * The disk check reads the real volume the tests run on, so thresholds are
 * pinned per test (config/health.php). Before that, "system healthy" failed
 * on any machine whose disk was more than 80% full. Logs are read from a
 * throwaway storage path so the developer's own laravel.log doesn't leak in.
 */
beforeEach(function () {
    $this->originalStoragePath = storage_path();
    $this->storageDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sista-health-'.uniqid();
    File::ensureDirectoryExists($this->storageDir.DIRECTORY_SEPARATOR.'logs');
    app()->useStoragePath($this->storageDir);

    config(['health.disk_warning_percent' => 100, 'health.disk_critical_percent' => 100]);
});

afterEach(function () {
    app()->useStoragePath($this->originalStoragePath);
    File::deleteDirectory($this->storageDir);
});

function writeHealthLog(string $contents): void
{
    file_put_contents(storage_path('logs/laravel.log'), $contents);
}

it('berhasil menjalankan perintah app:health-check dan mendeteksi sistem sehat', function () {
    // Jalankan perintah health check
    $exitCode = Artisan::call('app:health-check');

    // Pastikan exit code bernilai 0 (sehat)
    expect($exitCode)->toBe(0);

    // Ambil output console
    $output = Artisan::output();

    // Verifikasi output mencantumkan komponen-komponen penting
    expect($output)->toContain('SISTA System Operational Health Check')
        ->toContain('Database')
        ->toContain('Cache Store')
        ->toContain('Disk Space')
        ->toContain('Queue Runner')
        ->toContain('System Logs')
        ->toContain('No log files exist yet')
        ->toContain('SYSTEM HEALTHY');
});

it('mengembalikan status warning atau critical jika terjadi kesalahan pada cache store', function () {
    // Buat Cache melempar Exception dengan cara me-mock Cache
    Cache::shouldReceive('put')
        ->once()
        ->andThrow(new Exception('Cache store connection lost.'));

    $exitCode = Artisan::call('app:health-check');

    // Ketika ada system warning/critical, exit code adalah 1
    expect($exitCode)->toBe(1);

    $output = Artisan::output();
    expect($output)->toContain('CRITICAL')
        ->toContain('Cache store connection lost')
        ->toContain('SYSTEM WARNING');
});

it('menandai disk kritis ketika pemakaian melewati ambang', function () {
    config(['health.disk_warning_percent' => 0, 'health.disk_critical_percent' => 0]);

    expect(Artisan::call('app:health-check'))->toBe(1);
    expect(Artisan::output())->toContain('Disk Space')
        ->toContain('CRITICAL')
        ->toContain('SYSTEM WARNING');
});

it('hanya menghitung error di bagian akhir log', function () {
    config(['health.log_tail_lines' => 1500]);

    // An old error followed by 3000 clean lines is outside the scanned tail.
    writeHealthLog(
        "[2026-01-01 00:00:00] production.ERROR: kejadian lama\n"
        .str_repeat("[2026-01-02 00:00:00] production.INFO: ok\n", 3000)
    );
    expect(Artisan::call('app:health-check'))->toBe(0);
    expect(Artisan::output())->toContain('No recent errors registered in logs.');

    file_put_contents(
        storage_path('logs/laravel.log'),
        "[2026-09-26 10:00:00] production.ERROR: baru saja\n[2026-09-26 10:00:01] production.CRITICAL: parah\n",
        FILE_APPEND
    );
    Artisan::call('app:health-check');
    expect(Artisan::output())->toContain('Found 2 error(s) in recent logs.');
});

it('membaca log besar tanpa memuat seluruh file', function () {
    // ~8 MB log with the only error at the very end; only the last 64 KB is read.
    config(['health.log_tail_bytes' => 64 * 1024]);
    $handle = fopen(storage_path('logs/laravel.log'), 'wb');
    $line = str_repeat('x', 1000)."\n";
    for ($i = 0; $i < 8000; $i++) {
        fwrite($handle, $line);
    }
    fwrite($handle, "[2026-09-26 10:00:00] production.ERROR: di ujung log\n");
    fclose($handle);

    // Peak, not final, usage: a whole-file read is freed again by the time
    // the command returns, so only the peak shows it.
    $before = memory_get_usage();
    memory_reset_peak_usage();
    Artisan::call('app:health-check');
    $peakGrowth = memory_get_peak_usage() - $before;

    expect(Artisan::output())->toContain('Found 1 error(s) in recent logs.');
    // Reading the whole file would need at least its 8 MB (plus the exploded copy).
    expect($peakGrowth)->toBeLessThan(4 * 1024 * 1024);
});
