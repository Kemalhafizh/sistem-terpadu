<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappBroadcastJob;
use App\Models\Classroom;
use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappBroadcast;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappAutomationService;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WhatsappController extends Controller
{
    protected $whatsappService;

    protected $automationService;

    public function __construct(WhatsappService $whatsappService, WhatsappAutomationService $automationService)
    {
        $this->whatsappService = $whatsappService;
        $this->automationService = $automationService;
    }

    /**
     * Display WhatsApp sandbox simulator & settings.
     */
    public function index()
    {
        $logs = WhatsappLog::latest()->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => WhatsappLog::count(),
            'sent' => WhatsappLog::where('status', 'sent')->count(),
            'failed' => WhatsappLog::where('status', 'failed')->count(),
            'pending' => WhatsappLog::where('status', 'pending')->count(),
        ];

        // Fetch settings
        $settings = [
            'sandbox_mode' => Setting::get('whatsapp_sandbox_mode', '1'),
            'api_token' => Setting::get('whatsapp_api_token', ''),
            'sender' => Setting::get('whatsapp_sender', ''),
            'enable_attendance' => Setting::get('whatsapp_enable_attendance', '1'),
            'enable_point' => Setting::get('whatsapp_enable_point', '1'),
            'enable_billing' => Setting::get('whatsapp_enable_billing', '1'),
        ];

        return view('admin.whatsapp.index', compact('logs', 'stats', 'settings'));
    }

    /**
     * WhatsApp Gateway Dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'total' => WhatsappLog::count(),
            'sent' => WhatsappLog::where('status', 'sent')->count(),
            'failed' => WhatsappLog::where('status', 'failed')->count(),
            'pending' => WhatsappLog::where('status', 'pending')->count(),
            'sent_today' => WhatsappLog::whereDate('created_at', Carbon::today())->count(),
            'sent_this_month' => WhatsappLog::whereMonth('created_at', Carbon::now()->month)->count(),
        ];

        // Calculate delivery success rate
        $stats['success_rate'] = $stats['total'] > 0 ? round(($stats['sent'] / $stats['total']) * 100, 1) : 0;

        // Daily chart data for the last 7 days
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartData['labels'][] = $date->translatedFormat('d M');
            $chartData['sent'][] = WhatsappLog::whereDate('created_at', $date)->where('status', 'sent')->count();
            $chartData['failed'][] = WhatsappLog::whereDate('created_at', $date)->where('status', 'failed')->count();
        }

        return view('admin.whatsapp.dashboard', compact('stats', 'chartData'));
    }

    /**
     * Send a manual test WhatsApp message.
     */
    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'required|string',
            'message' => 'required|string',
        ]);

        $log = $this->whatsappService->sendMessage($validated['recipient'], $validated['message'], 'manual_test');

        if ($log->status === 'sent') {
            return back()->with('success', 'Uji coba pesan WhatsApp berhasil terkirim via '.(Setting::get('whatsapp_sandbox_mode', '1') === '1' ? 'Sandbox Mode' : 'Fonnte API'));
        }

        return back()->with('error', 'Gagal mengirim pesan: '.$log->error_message);
    }

    /**
     * Update WhatsApp Gateway settings.
     */
    public function updateSettings(Request $request)
    {
        Setting::set('whatsapp_sandbox_mode', $request->has('whatsapp_sandbox_mode') ? '1' : '0');
        Setting::set('whatsapp_api_token', $request->whatsapp_api_token ?? '');
        Setting::set('whatsapp_sender', $request->whatsapp_sender ?? '');
        Setting::set('whatsapp_enable_attendance', $request->has('whatsapp_enable_attendance') ? '1' : '0');
        Setting::set('whatsapp_enable_point', $request->has('whatsapp_enable_point') ? '1' : '0');
        Setting::set('whatsapp_enable_billing', $request->has('whatsapp_enable_billing') ? '1' : '0');

        return back()->with('success', 'Konfigurasi WhatsApp Gateway berhasil disimpan.');
    }

    /**
     * Resend an existing log entry.
     */
    public function resend(WhatsappLog $log)
    {
        $newLog = $this->whatsappService->sendMessage($log->recipient, $log->message, $log->type);

        if ($newLog->status === 'sent') {
            return back()->with('success', 'Pesan berhasil dikirim ulang.');
        }

        return back()->with('error', 'Gagal mengirim ulang: '.$newLog->error_message);
    }

    // ==========================================
    // TEMPLATE MANAGEMENT CRUD
    // ==========================================

    public function templates()
    {
        $templates = WhatsappTemplate::latest()->paginate(15);

        return view('admin.whatsapp.templates.index', compact('templates'));
    }

    public function createTemplate()
    {
        return view('admin.whatsapp.templates.create');
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:keuangan,akademik,keislaman,kegiatan,umum',
            'content' => 'required|string',
        ]);

        // Automatically extract placeholders from content like {nama_siswa}
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $validated['content'], $matches);
        $placeholders = array_values(array_unique($matches[1] ?? []));

        WhatsappTemplate::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'content' => $validated['content'],
            'placeholders' => $placeholders,
            'is_active' => true,
        ]);

        return redirect()->route('admin.whatsapp.templates.index')->with('success', 'Template baru berhasil disimpan.');
    }

    public function editTemplate($id)
    {
        $template = WhatsappTemplate::findOrFail($id);

        return view('admin.whatsapp.templates.edit', compact('template'));
    }

    public function updateTemplate($id, Request $request)
    {
        $template = WhatsappTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:keuangan,akademik,keislaman,kegiatan,umum',
            'content' => 'required|string',
        ]);

        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $validated['content'], $matches);
        $placeholders = array_values(array_unique($matches[1] ?? []));

        $template->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'content' => $validated['content'],
            'placeholders' => $placeholders,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.whatsapp.templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function deleteTemplate($id)
    {
        $template = WhatsappTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.whatsapp.templates.index')->with('success', 'Template berhasil dihapus.');
    }

    // ==========================================
    // BROADCAST MANAGEMENT
    // ==========================================

    public function broadcasts()
    {
        $broadcasts = WhatsappBroadcast::with('template')->latest()->paginate(15);

        return view('admin.whatsapp.broadcasts.index', compact('broadcasts'));
    }

    public function createBroadcast()
    {
        $templates = WhatsappTemplate::where('is_active', true)->get();
        $classrooms = Classroom::orderBy('name')->get();
        $users = User::whereNotNull('phone_number')->orderBy('name')->get(['id', 'name', 'role', 'phone_number']);

        return view('admin.whatsapp.broadcasts.create', compact('templates', 'classrooms', 'users'));
    }

    public function storeBroadcast(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required_without:template_id|string|nullable',
            'template_id' => 'required_without:message|exists:whatsapp_templates,id|nullable',
            'target_type' => 'required|in:all_parents,all_teachers,specific_class,custom',
            'target_ids' => 'required_if:target_type,specific_class,custom|array|nullable',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        // Get final message content
        $message = $validated['message'] ?? '';
        $templateId = $validated['template_id'] ?? null;
        if ($templateId) {
            $template = WhatsappTemplate::findOrFail($templateId);
            $message = $template->content;
        }

        $status = 'draft';
        if ($request->filled('scheduled_at')) {
            $status = 'scheduled';
        } else {
            $status = 'sending';
        }

        $broadcast = WhatsappBroadcast::create([
            'title' => $validated['title'],
            'template_id' => $templateId,
            'message' => $message,
            'target_type' => $validated['target_type'],
            'target_ids' => $validated['target_ids'] ?? null,
            'status' => $status,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if ($status === 'sending') {
            SendWhatsappBroadcastJob::dispatch($broadcast);
        }

        return redirect()->route('admin.whatsapp.broadcasts.index')->with('success', $status === 'scheduled' ? 'Siaran WhatsApp berhasil dijadwalkan.' : 'Siaran WhatsApp mulai dikirim.');
    }

    public function showBroadcast($id)
    {
        $broadcast = WhatsappBroadcast::with('template', 'creator')->findOrFail($id);

        // Find sent/failed logs for this broadcast
        $logs = WhatsappLog::where('type', 'broadcast_'.$broadcast->id)
            ->latest()
            ->paginate(30);

        return view('admin.whatsapp.broadcasts.show', compact('broadcast', 'logs'));
    }

    public function retryFailed($id)
    {
        $broadcast = WhatsappBroadcast::findOrFail($id);

        // Find failed logs for this broadcast
        $failedLogs = WhatsappLog::where('type', 'broadcast_'.$broadcast->id)
            ->where('status', 'failed')
            ->get();

        if ($failedLogs->isEmpty()) {
            return back()->with('info', 'Tidak ditemukan pengiriman yang gagal.');
        }

        foreach ($failedLogs as $log) {
            $this->whatsappService->sendMessage($log->recipient, $log->message, $log->type);
        }

        // Reset counts dynamically
        $totalLogs = WhatsappLog::where('type', 'broadcast_'.$broadcast->id)->get();
        $broadcast->update([
            'sent_count' => $totalLogs->where('status', 'sent')->count(),
            'failed_count' => $totalLogs->where('status', 'failed')->count(),
        ]);

        return back()->with('success', 'Mencoba mengirim ulang '.$failedLogs->count().' pesan yang gagal.');
    }

    // ==========================================
    // AUTOMATIONS TOGGLE & CRUD
    // ==========================================

    public function automations()
    {
        $automations = WhatsappAutomation::with('template')->get();
        $templates = WhatsappTemplate::where('is_active', true)->get();

        // If automations table is empty, let's pre-populate it dynamically for easy setup
        if ($automations->isEmpty() && $templates->isNotEmpty()) {
            $standardTriggers = [
                'billing_due_3_days' => 'Pemberitahuan Tagihan H-3',
                'billing_overdue' => 'Pemberitahuan Tagihan Terlambat',
                'attendance_absent' => 'Pemberitahuan Siswa Alpa',
                'exam_upcoming_1_day' => 'Pemberitahuan Ujian CBT Besok',
                'uks_visit_severe' => 'Pemberitahuan Darurat UKS',
                'grade_below_kkm' => 'Pemberitahuan Nilai di Bawah KKM',
                'mutabaah_grade_d' => "Pemberitahuan Mutaba'ah Kurang (Grade D)",
                'tahfidz_behind_target' => 'Pemberitahuan Hafalan Tahfidz Tertinggal',
            ];

            foreach ($standardTriggers as $trigger => $name) {
                WhatsappAutomation::create([
                    'name' => $name,
                    'trigger' => $trigger,
                    'template_id' => $templates->first()->id,
                    'is_active' => false,
                ]);
            }

            $automations = WhatsappAutomation::with('template')->get();
        }

        return view('admin.whatsapp.automations.index', compact('automations', 'templates'));
    }

    public function storeAutomation(Request $request)
    {
        $validated = $request->validate([
            'trigger' => 'required|in:billing_due_3_days,billing_overdue,attendance_absent,exam_upcoming_1_day,uks_visit_severe,grade_below_kkm,mutabaah_grade_d,tahfidz_behind_target',
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:whatsapp_templates,id',
            'config' => 'nullable|array',
        ]);

        WhatsappAutomation::updateOrCreate(
            ['trigger' => $validated['trigger']],
            [
                'name' => $validated['name'],
                'template_id' => $validated['template_id'],
                'config' => $validated['config'] ?? null,
            ]
        );

        return redirect()->route('admin.whatsapp.automations.index')->with('success', 'Aturan otomasi berhasil disimpan.');
    }

    public function toggleAutomation($id)
    {
        $automation = WhatsappAutomation::findOrFail($id);
        $automation->update([
            'is_active' => ! $automation->is_active,
        ]);

        return redirect()->route('admin.whatsapp.automations.index')->with('success', 'Status otomasi berhasil diubah.');
    }

    public function deleteAutomation($id)
    {
        $automation = WhatsappAutomation::findOrFail($id);
        $automation->delete();

        return redirect()->route('admin.whatsapp.automations.index')->with('success', 'Aturan otomasi berhasil dihapus.');
    }

    // ==========================================
    // FILTERABLE LOGS
    // ==========================================

    public function logs(Request $request)
    {
        $query = WhatsappLog::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', 'like', $request->input('type').'%');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $logs = $query->latest()->paginate(15);

        // Get filter stats
        $stats = [
            'total' => WhatsappLog::count(),
            'sent' => WhatsappLog::where('status', 'sent')->count(),
            'failed' => WhatsappLog::where('status', 'failed')->count(),
            'pending' => WhatsappLog::where('status', 'pending')->count(),
        ];

        return view('admin.whatsapp.logs', compact('logs', 'stats'));
    }
}
