<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledReport;
use App\Services\DigestService;
use Illuminate\Http\Request;

class ScheduledReportController extends Controller
{
    protected $digestService;

    public function __construct(DigestService $digestService)
    {
        $this->digestService = $digestService;
    }

    /**
     * Display a listing of scheduled reports.
     */
    public function index()
    {
        $reports = ScheduledReport::orderBy('created_at', 'desc')->get();
        
        $types = [
            'daily_admin' => 'Harian Administrator (Daily Admin)',
            'weekly_kepsek' => 'Mingguan Kepala Sekolah (Weekly Headmaster)',
            'monthly_finance' => 'Bulanan Keuangan (Monthly Finance)',
            'weekly_parent' => 'Mingguan Orang Tua (Weekly Parents)',
        ];

        return view('admin.scheduled-reports.index', compact('reports', 'types'));
    }

    /**
     * Store a newly created scheduled report in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|in:daily_admin,weekly_kepsek,monthly_finance,weekly_parent',
            'cron_expression' => 'required|string',
            'recipients' => 'required|array|min:1',
            'recipients.*.type' => 'required|in:email,role',
            'recipients.*.value' => 'required|string',
            'delivery_method' => 'required|in:email,whatsapp,both',
            'attach_pdf' => 'boolean',
            'is_active' => 'boolean',
        ]);

        ScheduledReport::create([
            'name' => $request->name,
            'report_type' => $request->report_type,
            'cron_expression' => $request->cron_expression,
            'recipients' => $request->recipients,
            'delivery_method' => $request->delivery_method,
            'attach_pdf' => $request->has('attach_pdf') ? (bool)$request->attach_pdf : true,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Jadwal laporan baru berhasil dibuat.');
    }

    /**
     * Update the specified scheduled report in storage.
     */
    public function update(Request $request, ScheduledReport $report)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|in:daily_admin,weekly_kepsek,monthly_finance,weekly_parent',
            'cron_expression' => 'required|string',
            'recipients' => 'required|array|min:1',
            'recipients.*.type' => 'required|in:email,role',
            'recipients.*.value' => 'required|string',
            'delivery_method' => 'required|in:email,whatsapp,both',
            'attach_pdf' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $report->update([
            'name' => $request->name,
            'report_type' => $request->report_type,
            'cron_expression' => $request->cron_expression,
            'recipients' => $request->recipients,
            'delivery_method' => $request->delivery_method,
            'attach_pdf' => $request->has('attach_pdf') ? (bool)$request->attach_pdf : false,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : false,
        ]);

        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Konfigurasi jadwal laporan berhasil diperbarui.');
    }

    /**
     * Remove the specified scheduled report from storage.
     */
    public function destroy(ScheduledReport $report)
    {
        $report->delete();
        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Jadwal laporan berhasil dihapus.');
    }

    /**
     * Instantly trigger a scheduled report for testing/debugging purposes.
     */
    public function triggerImmediate(ScheduledReport $report)
    {
        try {
            $this->digestService->sendDigest($report);
            return redirect()->route('admin.scheduled-reports.index')
                ->with('success', "Aksi Kirim Sekarang berhasil dipicu. Laporan '{$report->name}' telah dikirim.");
        } catch (\Exception $e) {
            return redirect()->route('admin.scheduled-reports.index')
                ->with('error', "Gagal mengirim laporan terjadwal secara instan: " . $e->getMessage());
        }
    }
}
