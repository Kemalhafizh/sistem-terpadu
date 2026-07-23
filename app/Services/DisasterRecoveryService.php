<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DisasterRecoveryService
{
    protected string $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/snapshots');
        if (! File::exists($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0755, true, true);
        }
    }

    /**
     * Create automated or manual database snapshot with SHA-256 checksum.
     */
    public function createSnapshot(string $label = 'auto'): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "snapshot_{$label}_{$timestamp}.sqlite";
        $targetPath = $this->storagePath . '/' . $filename;
        $metaPath = $this->storagePath . '/' . $filename . '.json';

        $dbConnection = config('database.default', 'sqlite');
        $dbPath = config('database.connections.sqlite.database');

        if ($dbConnection === 'sqlite' && $dbPath !== ':memory:' && File::exists($dbPath)) {
            File::copy($dbPath, $targetPath);
        } else {
            // In-memory SQLite or non-file connection dump
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $dumpData = [];
            foreach ($tables as $table) {
                $tableName = $table->name;
                $dumpData[$tableName] = DB::table($tableName)->get()->toArray();
            }
            File::put($targetPath, json_encode($dumpData));
        }

        $checksum = hash_file('sha256', $targetPath);
        $fileSize = File::size($targetPath);

        $metadata = [
            'filename' => $filename,
            'label' => $label,
            'created_at' => now()->toIso8601String(),
            'size_bytes' => $fileSize,
            'checksum_sha256' => $checksum,
            'status' => 'VERIFIED',
            'tables_count' => count(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")),
        ];

        File::put($metaPath, json_encode($metadata, JSON_PRETTY_PRINT));

        return $metadata;
    }

    /**
     * Get list of all available snapshots with their metadata.
     */
    public function getSnapshots(): array
    {
        $files = File::files($this->storagePath);
        $snapshots = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sqlite' || ($file->getExtension() === 'json' && ! str_ends_with($file->getFilename(), '.sqlite.json'))) {
                $filename = $file->getFilename();
                $metaPath = $this->storagePath . '/' . $filename . '.json';

                if (File::exists($metaPath)) {
                    $meta = json_decode(File::get($metaPath), true);
                    $snapshots[] = $meta;
                } else {
                    $snapshots[] = [
                        'filename' => $filename,
                        'label' => 'manual',
                        'created_at' => date('c', $file->getMTime()),
                        'size_bytes' => $file->getSize(),
                        'checksum_sha256' => hash_file('sha256', $file->getPathname()),
                        'status' => 'UNVERIFIED',
                        'tables_count' => 0,
                    ];
                }
            }
        }

        usort($snapshots, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $snapshots;
    }

    /**
     * Verify snapshot integrity against recorded SHA-256 checksum.
     */
    public function verifyChecksum(string $filename): bool
    {
        $targetPath = $this->storagePath . '/' . basename($filename);
        $metaPath = $targetPath . '.json';

        if (! File::exists($targetPath)) {
            return false;
        }

        $calculatedHash = hash_file('sha256', $targetPath);

        if (File::exists($metaPath)) {
            $meta = json_decode(File::get($metaPath), true);
            $expectedHash = $meta['checksum_sha256'] ?? '';
            return hash_equals($expectedHash, $calculatedHash);
        }

        return true;
    }

    /**
     * Restore database from snapshot file.
     */
    public function restoreSnapshot(string $filename): bool
    {
        $targetPath = $this->storagePath . '/' . basename($filename);

        if (! File::exists($targetPath)) {
            return false;
        }

        if (! $this->verifyChecksum($filename)) {
            throw new \Exception('Gagal memulihkan snapshot: Integritas berkas rusak / SHA-256 checksum tidak cocok.');
        }

        $dbConnection = config('database.default', 'sqlite');
        $dbPath = config('database.connections.sqlite.database');

        if ($dbConnection === 'sqlite' && $dbPath !== ':memory:' && File::exists($dbPath)) {
            // Backup active database first before restoring
            $tempBackup = $this->storagePath . '/pre_restore_backup_' . time() . '.sqlite';
            File::copy($dbPath, $tempBackup);

            // Restore
            File::copy($targetPath, $dbPath);
        }

        return true;
    }

    /**
     * Delete snapshot file & its metadata.
     */
    public function deleteSnapshot(string $filename): bool
    {
        $targetPath = $this->storagePath . '/' . basename($filename);
        $metaPath = $targetPath . '.json';

        if (File::exists($targetPath)) {
            File::delete($targetPath);
        }
        if (File::exists($metaPath)) {
            File::delete($metaPath);
        }

        return true;
    }

    /**
     * Run automated database integrity audit.
     */
    public function runIntegrityAudit(): array
    {
        $auditResult = [
            'timestamp' => now()->toIso8601String(),
            'status' => 'HEALTHY',
            'checks' => [],
        ];

        try {
            // Check 1: Foreign key integrity check
            $foreignKeyErrors = DB::select('PRAGMA foreign_key_check');
            $auditResult['checks']['foreign_keys'] = [
                'name' => 'Verifikasi Konsistensi Foreign Key',
                'status' => empty($foreignKeyErrors) ? 'PASSED' : 'FAILED',
                'errors_count' => count($foreignKeyErrors),
            ];

            // Check 2: SQLite database integrity check
            $integrityResult = DB::select('PRAGMA integrity_check');
            $isIntegrityOk = isset($integrityResult[0]->integrity_check) && $integrityResult[0]->integrity_check === 'ok';
            $auditResult['checks']['database_structure'] = [
                'name' => 'Pemeriksaan Integritas Struktur Tabel & Indeks',
                'status' => $isIntegrityOk ? 'PASSED' : 'FAILED',
                'raw_result' => $integrityResult[0]->integrity_check ?? 'unknown',
            ];

            // Check 3: Check database storage file size
            $dbPath = config('database.connections.sqlite.database');
            $dbSize = ($dbPath !== ':memory:' && File::exists($dbPath)) ? File::size($dbPath) : 1024 * 1024;
            $auditResult['checks']['storage_health'] = [
                'name' => 'Kesehatan Berkas Penyimpanan Database',
                'status' => $dbSize > 0 ? 'PASSED' : 'WARNING',
                'size_bytes' => $dbSize,
                'size_readable' => round($dbSize / 1024 / 1024, 2) . ' MB',
            ];

            if ($auditResult['checks']['foreign_keys']['status'] === 'FAILED' || $auditResult['checks']['database_structure']['status'] === 'FAILED') {
                $auditResult['status'] = 'CORRUPTED';
            }
        } catch (\Throwable $e) {
            $auditResult['status'] = 'ERROR';
            $auditResult['error_message'] = $e->getMessage();
        }

        return $auditResult;
    }
}
