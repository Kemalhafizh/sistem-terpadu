<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Pastikan folder disk cloud_mock dibersihkan sebelum tes
    Storage::disk('cloud_mock')->deleteDirectory('Sistem-Terpadu-Backup');
});

it('berhasil menjalankan perintah backup:run dan menyimpan arsip cadangan di disk cloud_mock', function () {
    // Hindari backup database in-memory :memory: dengan mengosongkan daftar database yang dibackup saat test
    config(['backup.backup.source.databases' => []]);

    // Pastikan folder public ada agar tidak dilewati
    Storage::disk('public')->put('temp-backup-test.txt', 'test content');

    // Jalankan perintah backup hanya untuk file guna menghindari ketergantungan pada binary database eksternal di test environment
    $exitCode = Artisan::call('backup:run', [
        '--only-files' => true,
        '--disable-notifications' => true,
    ]);

    expect($exitCode)->toBe(0);

    // Dapatkan daftar file di disk cloud_mock
    $files = Storage::disk('cloud_mock')->allFiles('Sistem-Terpadu-Backup');

    // Verifikasi bahwa setidaknya ada 1 berkas backup zip yang terbuat
    expect(count($files))->toBeGreaterThanOrEqual(1);
    expect($files[0])->toContain('.zip');
});
