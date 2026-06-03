<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class QueueMonitorController extends Controller
{
    public function index()
    {
        // Read pending jobs from 'jobs' table
        $pendingJobs = DB::table('jobs')->orderBy('id', 'desc')->get();

        // Read failed jobs from 'failed_jobs' table
        $failedJobs = DB::table('failed_jobs')->orderBy('id', 'desc')->get();

        $metrics = [
            'pending_count' => $pendingJobs->count(),
            'failed_count' => $failedJobs->count(),
        ];

        return view('admin.queue.index', compact('pendingJobs', 'failedJobs', 'metrics'));
    }

    public function retry($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => $id]);

            return back()->with('success', "Job #{$id} has been queued for retry.");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to retry job #{$id}: ".$e->getMessage());
        }
    }

    public function retryAll()
    {
        try {
            Artisan::call('queue:retry', ['id' => 'all']);

            return back()->with('success', 'All failed jobs have been queued for retry.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry jobs: '.$e->getMessage());
        }
    }

    public function flush()
    {
        try {
            Artisan::call('queue:flush');

            return back()->with('success', 'All failed jobs have been cleared.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear jobs: '.$e->getMessage());
        }
    }
}
