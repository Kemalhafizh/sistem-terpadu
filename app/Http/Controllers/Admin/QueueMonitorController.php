<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePdfReportJob;
use App\Jobs\ProcessCbtScoreJob;
use App\Jobs\SendHighPriorityNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Exception;

class QueueMonitorController extends Controller
{
    /**
     * Display Queue Pipeline & Worker Monitoring Dashboard.
     */
    public function index()
    {
        $highQueueCount = DB::table('jobs')->where('queue', 'high')->count();
        $defaultQueueCount = DB::table('jobs')->where('queue', 'default')->count();
        $lowQueueCount = DB::table('jobs')->where('queue', 'low')->count();
        $totalPending = $highQueueCount + $defaultQueueCount + $lowQueueCount;

        $failedJobsCount = DB::table('failed_jobs')->count();
        $failedJobs = DB::table('failed_jobs')->latest('failed_at')->paginate(15);

        return view('admin.observability.queues', compact(
            'highQueueCount',
            'defaultQueueCount',
            'lowQueueCount',
            'totalPending',
            'failedJobsCount',
            'failedJobs'
        ));
    }

    /**
     * Retry a specific failed job from Dead Letter Queue.
     */
    public function retryFailedJob($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            return back()->with('success', "Pekerjaan gagal (ID #{$id}) berhasil dikirim ulang ke queue worker!");
        } catch (Exception $e) {
            return back()->with('error', "Gagal mengulang pekerjaan: {$e->getMessage()}");
        }
    }

    /**
     * Delete a specific failed job from Dead Letter Queue.
     */
    public function deleteFailedJob($id)
    {
        try {
            DB::table('failed_jobs')->where('id', $id)->delete();
            return back()->with('success', "Pekerjaan gagal (ID #{$id}) berhasil dihapus dari sistem.");
        } catch (Exception $e) {
            return back()->with('error', "Gagal menghapus pekerjaan: {$e->getMessage()}");
        }
    }

    /**
     * Flush all failed jobs in Dead Letter Queue.
     */
    public function flushFailedJobs()
    {
        try {
            DB::table('failed_jobs')->truncate();
            return back()->with('success', "Seluruh daftar pekerjaan gagal (Dead Letter Queue) berhasil dibersihkan!");
        } catch (Exception $e) {
            return back()->with('error', "Gagal membersihkan DLQ: {$e->getMessage()}");
        }
    }

    /**
     * Dispatch a test job to high, default, or low queue pipeline.
     */
    public function dispatchDemoJob(Request $request)
    {
        $request->validate([
            'queue_priority' => 'required|in:high,default,low',
        ]);

        $priority = $request->queue_priority;

        match ($priority) {
            'high' => SendHighPriorityNotificationJob::dispatch('081234567890', 'Notifikasi Prioritas Tinggi WhatsApp Demo'),
            'default' => ProcessCbtScoreJob::dispatch(999),
            'low' => GeneratePdfReportJob::dispatch('Rapor Semester Ganjil', 101),
        };

        return back()->with('success', "Berhasil mengirimkan Pekerjaan Demo Latar Belakang ke Pipeline Queue: [{$priority}]!");
    }
}
