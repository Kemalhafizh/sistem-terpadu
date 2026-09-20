<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationConfig;
use App\Models\IntegrationLog;
use App\Models\Webhook;
use App\Models\Classroom;
use App\Services\Integration\DapodikService;
use App\Services\Integration\EmisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class IntegrationController extends Controller
{
    protected $dapodikService;
    protected $emisService;

    public function __construct(DapodikService $dapodikService, EmisService $emisService)
    {
        $this->dapodikService = $dapodikService;
        $this->emisService = $emisService;
    }

    /**
     * Dashboard Index.
     */
    public function index()
    {
        // Ensure default configurations exist
        $dapodik = IntegrationConfig::firstOrCreate(
            ['provider' => 'dapodik'],
            ['name' => 'Koneksi Dapodik SMA ISA 1', 'is_active' => true]
        );

        $emis = IntegrationConfig::firstOrCreate(
            ['provider' => 'emis'],
            ['name' => 'Koneksi EMIS Kemenag', 'is_active' => true]
        );

        $yayasan = IntegrationConfig::firstOrCreate(
            ['provider' => 'yayasan'],
            ['name' => 'Integrasi Yayasan Badan Wakaf', 'is_active' => false]
        );

        $logs = IntegrationLog::with('config')->latest()->take(10)->get();
        $webhooks = Webhook::all();

        // Calculate statistics
        $totalSuccess = IntegrationLog::where('status', 'success')->count();
        $totalFailed = IntegrationLog::where('status', 'failed')->count();

        return view('integrations.index', compact('dapodik', 'emis', 'yayasan', 'logs', 'webhooks', 'totalSuccess', 'totalFailed'));
    }

    /**
     * Configuration panel for Dapodik.
     */
    public function configDapodik(Request $request)
    {
        $type = $request->get('type', 'students');
        $classroomId = $request->get('classroom_id');
        $classrooms = Classroom::all();

        $previewData = [];
        $validationWarnings = [];

        if ($type === 'students') {
            $allData = $this->dapodikService->exportStudentData($classroomId);
            $previewData = array_slice($allData, 0, 10);
            $validationWarnings = $this->dapodikService->validateAgainstDapodik('students', $allData);
        } elseif ($type === 'teachers') {
            $allData = $this->dapodikService->exportTeacherData();
            $previewData = array_slice($allData, 0, 10);
            $validationWarnings = $this->dapodikService->validateAgainstDapodik('teachers', $allData);
        } elseif ($type === 'classrooms') {
            $allData = $this->dapodikService->exportClassroomData();
            $previewData = array_slice($allData, 0, 10);
        } elseif ($type === 'attendances') {
            $month = $request->get('month', date('Y-m'));
            $allData = $this->dapodikService->exportAttendanceData($month, $classroomId);
            $previewData = array_slice($allData, 0, 10);
        }

        return view('integrations.dapodik', compact('type', 'classroomId', 'classrooms', 'previewData', 'validationWarnings'));
    }

    /**
     * Trigger Dapodik Excel Export.
     */
    public function exportDapodik(Request $request)
    {
        $type = $request->get('type', 'students');
        $classroomId = $request->get('classroom_id');
        $month = $request->get('month', date('Y-m'));

        $config = IntegrationConfig::where('provider', 'dapodik')->first();

        try {
            $export = $this->dapodikService->generateDapodikExcel($type, $classroomId, $month);
            
            $allData = [];
            if ($type === 'students') {
                $allData = $this->dapodikService->exportStudentData($classroomId);
            } elseif ($type === 'teachers') {
                $allData = $this->dapodikService->exportTeacherData();
            } elseif ($type === 'classrooms') {
                $allData = $this->dapodikService->exportClassroomData();
            } elseif ($type === 'attendances') {
                $allData = $this->dapodikService->exportAttendanceData($month, $classroomId);
            }

            $warnings = $this->dapodikService->validateAgainstDapodik($type, $allData);

            // Save success log
            IntegrationLog::create([
                'config_id' => $config->id,
                'direction' => 'export',
                'status' => empty($warnings) ? 'success' : 'partial',
                'records_processed' => count($allData),
                'records_failed' => count($warnings),
                'error_details' => $warnings,
                'summary' => "Berhasil mengekspor data {$type} Dapodik ke Excel.",
            ]);

            $config->update(['last_sync_at' => now()]);

            $filename = 'Dapodik_' . ucfirst($type) . '_' . date('Ymd_His') . '.xlsx';
            return Excel::download($export, $filename);

        } catch (\Exception $e) {
            // Save failure log
            IntegrationLog::create([
                'config_id' => $config->id,
                'direction' => 'export',
                'status' => 'failed',
                'records_processed' => 0,
                'records_failed' => 0,
                'error_details' => [['message' => $e->getMessage()]],
                'summary' => "Gagal mengekspor data {$type} Dapodik: " . $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal memproses ekspor Dapodik: ' . $e->getMessage());
        }
    }

    /**
     * Configuration panel for EMIS.
     */
    public function configEmis(Request $request)
    {
        $type = $request->get('type', 'students');
        $classroomId = $request->get('classroom_id');
        $classrooms = Classroom::all();

        $previewData = [];

        if ($type === 'students') {
            $allData = $this->emisService->exportStudentData($classroomId);
            $previewData = array_slice($allData, 0, 10);
        } elseif ($type === 'teachers') {
            $allData = $this->emisService->exportTeacherData();
            $previewData = array_slice($allData, 0, 10);
        } elseif ($type === 'classrooms') {
            $allData = $this->emisService->exportClassroomData();
            $previewData = array_slice($allData, 0, 10);
        }

        return view('integrations.emis', compact('type', 'classroomId', 'classrooms', 'previewData'));
    }

    /**
     * Trigger EMIS Excel Export.
     */
    public function exportEmis(Request $request)
    {
        $type = $request->get('type', 'students');
        $classroomId = $request->get('classroom_id');

        $config = IntegrationConfig::where('provider', 'emis')->first();

        try {
            $export = $this->emisService->generateEmisExcel($type, $classroomId);
            
            $allData = [];
            if ($type === 'students') {
                $allData = $this->emisService->exportStudentData($classroomId);
            } elseif ($type === 'teachers') {
                $allData = $this->emisService->exportTeacherData();
            } elseif ($type === 'classrooms') {
                $allData = $this->emisService->exportClassroomData();
            }

            // Save success log
            IntegrationLog::create([
                'config_id' => $config->id,
                'direction' => 'export',
                'status' => 'success',
                'records_processed' => count($allData),
                'records_failed' => 0,
                'summary' => "Berhasil mengekspor data {$type} EMIS Kemenag ke Excel.",
            ]);

            $config->update(['last_sync_at' => now()]);

            $filename = 'EMIS_' . ucfirst($type) . '_' . date('Ymd_His') . '.xlsx';
            return Excel::download($export, $filename);

        } catch (\Exception $e) {
            // Save failure log
            IntegrationLog::create([
                'config_id' => $config->id,
                'direction' => 'export',
                'status' => 'failed',
                'records_processed' => 0,
                'records_failed' => 0,
                'error_details' => [['message' => $e->getMessage()]],
                'summary' => "Gagal mengekspor data {$type} EMIS: " . $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal memproses ekspor EMIS: ' . $e->getMessage());
        }
    }

    /**
     * Outgoing Webhooks Management & CRUD.
     */
    public function webhooks()
    {
        $webhooks = Webhook::latest()->get();
        return view('integrations.webhooks', compact('webhooks'));
    }

    public function storeWebhook(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'secret' => 'nullable|string|max:255',
            'events' => 'required|array',
        ]);

        try {
            $validatedUrl = \App\Services\Security\SsrfGuardService::validateUrl($request->url);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', 'URL Webhook ditolak demi keamanan (SSRF Protection): ' . $e->getMessage());
        }

        Webhook::create([
            'name' => $request->name,
            'url' => $validatedUrl,
            'secret' => $request->secret,
            'events' => $request->events,
            'is_active' => true,
        ]);

        return redirect()->route('admin.integrations.webhooks.index')->with('success', 'Webhook berhasil terdaftar.');
    }

    public function updateWebhook(Request $request, Webhook $webhook)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'secret' => 'nullable|string|max:255',
            'events' => 'required|array',
        ]);

        try {
            $validatedUrl = \App\Services\Security\SsrfGuardService::validateUrl($request->url);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', 'URL Webhook ditolak demi keamanan (SSRF Protection): ' . $e->getMessage());
        }

        $webhook->update([
            'name' => $request->name,
            'url' => $validatedUrl,
            'secret' => $request->secret,
            'events' => $request->events,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.integrations.webhooks.index')->with('success', 'Webhook berhasil diperbarui.');
    }

    public function destroyWebhook(Webhook $webhook)
    {
        $webhook->delete();
        return redirect()->route('admin.integrations.webhooks.index')->with('success', 'Webhook berhasil dihapus.');
    }

    /**
     * Test Ping Webhook.
     */
    public function testWebhook(Webhook $webhook)
    {
        $payload = [
            'event' => 'student.created',
            'timestamp' => now()->timestamp,
            'data' => [
                'id' => 1,
                'name' => 'Ahmad Fauzi',
                'nisn' => '0098765432',
                'gender' => 'L',
                'kelas' => 'X-A',
            ]
        ];

        $signature = $webhook->secret ? hash_hmac('sha256', json_encode($payload), $webhook->secret) : 'none';

        try {
            \App\Services\Security\SsrfGuardService::validateUrl($webhook->url);

            $startTime = microtime(true);
            $response = Http::withHeaders([
                'X-Sista-Signature' => $signature,
                'Content-Type' => 'application/json',
            ])->timeout(5)->post($webhook->url, $payload);
            
            $latency = round((microtime(true) - $startTime) * 1000);

            $webhook->update([
                'last_triggered_at' => now(),
                'failure_count' => $response->successful() ? 0 : ($webhook->failure_count + 1),
            ]);

            return response()->json([
                'status' => $response->status(),
                'successful' => $response->successful(),
                'latency_ms' => $latency,
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            $webhook->update([
                'last_triggered_at' => now(),
                'failure_count' => $webhook->failure_count + 1,
            ]);

            return response()->json([
                'status' => 500,
                'successful' => false,
                'latency_ms' => 0,
                'body' => 'Gagal terhubung ke endpoint tujuan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * View Audit Logs.
     */
    public function logs()
    {
        $logs = IntegrationLog::with('config')->latest()->paginate(15);
        return view('integrations.logs', compact('logs'));
    }
}
