<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\WhatsappLog;
use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
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
}
