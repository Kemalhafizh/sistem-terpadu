<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\ExportJob;
use App\Services\Content\ExportEngineService;
use Illuminate\Http\Request;

class ExportEngineController extends Controller
{
    /**
     * Display Multi-Format Export Engine Format Selector & History.
     */
    public function selectFormat()
    {
        $exportHistory = ExportJob::with('user')
            ->orderBy('created_at', 'desc')
            ->take(25)
            ->get();

        return view('content.exports.format-selector', compact('exportHistory'));
    }

    /**
     * Trigger batch export generation.
     */
    public function generateExport(Request $request, ExportEngineService $exportService)
    {
        $request->validate([
            'job_name' => 'required|string|max:200',
            'entity_type' => 'required|string',
            'format' => 'required|in:pdf_a,xlsx,csv,json_ld,xml_dapodik',
        ]);

        $userId = auth()->id();
        $job = $exportService->queueExport(
            $request->job_name,
            $request->entity_type,
            $request->format,
            $userId
        );

        return back()->with('success', "Proses ekspor berkas [{$job->job_name}] berformat [".strtoupper($job->format).'] berhasil dijalankan!');
    }

    /**
     * Download completed export file.
     */
    public function downloadExport(int $id)
    {
        $job = ExportJob::findOrFail($id);

        if ($job->status !== 'completed' || ! $job->file_path) {
            return back()->with('error', 'Berkas ekspor belum selesai diproses atau gagal.');
        }

        // Return inline dummy text download stream for demonstration
        $content = "SISTA EXPORT ENGINE -- SULTAN AGUNG 1\nFORMAT: ".strtoupper($job->format)."\nJOB: {$job->job_name}\nCREATED AT: {$job->created_at}\nSTATUS: VALID VERIFIED";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$job->job_name}.txt\"",
        ]);
    }

    /**
     * View Export History Log.
     */
    public function exportHistory()
    {
        $history = ExportJob::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('content.exports.history', compact('history'));
    }
}
