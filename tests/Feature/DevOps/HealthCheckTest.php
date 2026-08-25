<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

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
        ->toContain('SYSTEM HEALTHY');
});

it('mengembalikan status warning atau critical jika terjadi kesalahan pada cache store', function () {
    // Buat Cache melempar Exception dengan cara me-mock Cache
    Cache::shouldReceive('put')
        ->once()
        ->andThrow(new \Exception('Cache store connection lost.'));

    $exitCode = Artisan::call('app:health-check');

    // Ketika ada system warning/critical, exit code adalah 1
    expect($exitCode)->toBe(1);

    $output = Artisan::output();
    expect($output)->toContain('CRITICAL')
        ->toContain('Cache store connection lost')
        ->toContain('SYSTEM WARNING');
});
