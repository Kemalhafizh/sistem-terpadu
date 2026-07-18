<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyncConfig;
use App\Models\SyncReport;
use App\Services\Integration\SyncMapperService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class SyncController extends Controller
{
    protected $syncService;

    public function __construct(SyncMapperService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display general sync integrations dashboard.
     */
    public function dashboard()
    {
        $reports = SyncReport::with('runBy')->latest()->take(10)->get();

        // Audit summary counters
        $dapodikStudentIssuesCount = count($this->syncService->validateData('dapodik', 'students'));
        $dapodikTeacherIssuesCount = count($this->syncService->validateData('dapodik', 'teachers'));
        
        $emisStudentIssuesCount = count($this->syncService->validateData('emis', 'students'));
        $emisTeacherIssuesCount = count($this->syncService->validateData('emis', 'teachers'));

        $totalIssuesCount = $dapodikStudentIssuesCount + $dapodikTeacherIssuesCount + $emisStudentIssuesCount + $emisTeacherIssuesCount;

        return view('integrations.sync.dashboard', compact(
            'reports',
            'dapodikStudentIssuesCount',
            'dapodikTeacherIssuesCount',
            'emisStudentIssuesCount',
            'emisTeacherIssuesCount',
            'totalIssuesCount'
        ));
    }

    /**
     * Show column mapper form.
     */
    public function mappingForm($target, $table)
    {
        if (! in_array($target, ['dapodik', 'emis']) || ! in_array($table, ['students', 'teachers'])) {
            abort(404);
        }

        $config = SyncConfig::where('system_target', $target)
            ->where('local_table', $table)
            ->first();

        // Default targets mapping dictionary
        $targetFields = [];
        if ($table === 'students') {
            $targetFields = [
                'nomor_induk' => 'Nomor Induk Siswa (NIS)',
                'nomor_statistik' => 'Nomor Statistik Siswa / EMIS ID',
                'nisn' => 'NISN Resmi',
                'nama_lengkap' => 'Nama Lengkap Siswa',
                'nama' => 'Nama Siswa (EMIS)',
                'jenis_kelamin' => 'Jenis Kelamin (L/P)',
                'tanggal_lahir' => 'Tanggal Lahir',
                'alamat' => 'Alamat Domisili',
                'alamat_lengkap' => 'Alamat Lengkap (EMIS)',
                'rombongan_belajar' => 'Nama Rombel / Kelas',
                'kelas' => 'Kelas (EMIS)',
            ];
        } else {
            $targetFields = [
                'nama_ptk' => 'Nama Pendidik/PTK',
                'nama_lengkap' => 'Nama Lengkap Pendidik',
                'email_aktif' => 'Email Aktif',
                'surel' => 'Surel / Email (EMIS)',
                'nip' => 'NIP Pegawai',
                'nip_pegawai' => 'NIP (EMIS)',
                'status_kepegawaian' => 'Status Kepegawaian',
                'status' => 'Status (EMIS)',
            ];
        }

        $localFields = [];
        if ($table === 'students') {
            $localFields = [
                'nis' => 'NIS Lokal',
                'nisn' => 'NISN',
                'name' => 'Nama Lengkap (User)',
                'gender' => 'Jenis Kelamin',
                'birth_date' => 'Tanggal Lahir',
                'address' => 'Alamat',
                'classroom' => 'Kelas',
            ];
        } else {
            $localFields = [
                'name' => 'Nama Lengkap (User)',
                'email' => 'Email (User)',
                'nip' => 'NIP Pegawai (Employee)',
                'status' => 'Status Pegawai (Employee)',
            ];
        }

        $mapping = $config ? $config->field_mapping : $this->syncService->getDefaultMapping($target, $table);

        return view('integrations.sync.mapping', compact('target', 'table', 'targetFields', 'localFields', 'mapping'));
    }

    /**
     * Save column mapper configuration.
     */
    public function saveMapping(Request $request)
    {
        $request->validate([
            'system_target' => 'required|in:dapodik,emis',
            'local_table' => 'required|in:students,teachers',
            'mapping' => 'required|array',
        ]);

        SyncConfig::updateOrCreate(
            [
                'system_target' => $request->system_target,
                'local_table' => $request->local_table,
            ],
            [
                'field_mapping' => $request->mapping,
            ]
        );

        return redirect()->route('admin.integration.sync.dashboard')
            ->with('success', 'Pemetaan kolom sinkronisasi berhasil disimpan.');
    }

    /**
     * Show data audit warnings before export.
     */
    public function runValidation($target, $table)
    {
        if (! in_array($target, ['dapodik', 'emis']) || ! in_array($table, ['students', 'teachers'])) {
            abort(404);
        }

        $issues = $this->syncService->validateData($target, $table);

        return view('integrations.sync.validation', compact('target', 'table', 'issues'));
    }

    /**
     * Trigger export to JSON ready for upload.
     */
    public function exportData($target, $table, Request $request)
    {
        if (! in_array($target, ['dapodik', 'emis']) || ! in_array($table, ['students', 'teachers'])) {
            abort(404);
        }

        // Get mapping config
        $config = SyncConfig::where('system_target', $target)
            ->where('local_table', $table)
            ->first();

        $mapping = $config ? $config->field_mapping : $this->syncService->getDefaultMapping($target, $table);

        try {
            // Map the data
            $mappedData = $this->syncService->mapData($target, $table, $mapping);

            // Audit
            $issues = $this->syncService->validateData($target, $table);
            $failedCount = count(array_filter($issues, fn($i) => $i['severity'] === 'error'));
            $processedCount = count($mappedData);
            $successCount = $processedCount - $failedCount;

            // Save report
            $this->syncService->saveReport(
                $target,
                $table,
                $processedCount,
                $successCount,
                $failedCount,
                $issues,
                auth()->id()
            );

            // Export to JSON file download
            $filename = "Sync_{$target}_{$table}_" . date('Ymd_His') . '.json';
            $json = json_encode([
                'metadata' => [
                    'target' => $target,
                    'table' => $table,
                    'exported_at' => now()->toDateTimeString(),
                    'total_records' => $processedCount,
                ],
                'data' => $mappedData,
            ], JSON_PRETTY_PRINT);

            return response($json, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses ekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Show audit history list.
     */
    public function syncHistory()
    {
        $reports = SyncReport::with('runBy')->latest()->paginate(15);
        return view('integrations.sync.history', compact('reports'));
    }
}
