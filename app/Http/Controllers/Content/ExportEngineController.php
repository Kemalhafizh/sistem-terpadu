<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\ExportJob;
use App\Services\Content\ExportEngineService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExportEngineController extends Controller
{
    /**
     * Export form and the latest jobs.
     */
    public function selectFormat(Request $request)
    {
        $exportHistory = $this->visibleJobs($request)->take(25)->get();
        $entities = ExportEngineService::entitiesFor($request->user()->role);
        $formats = ExportEngineService::FORMATS;

        return view('content.exports.format-selector', compact('exportHistory', 'entities', 'formats'));
    }

    /**
     * Build an export file from the database.
     */
    public function generateExport(Request $request, ExportEngineService $exportService)
    {
        $validated = $request->validate([
            'job_name' => 'required|string|max:200',
            'entity_type' => ['required', Rule::in(array_keys(ExportEngineService::entitiesFor($request->user()->role)))],
            'format' => ['required', Rule::in(array_keys(ExportEngineService::FORMATS))],
        ], [
            'entity_type.in' => 'Anda tidak berwenang mengekspor data tersebut.',
        ]);

        $job = $exportService->queueExport(
            $validated['job_name'],
            $validated['entity_type'],
            $validated['format'],
            $request->user()->id
        );

        if ($job->status !== 'completed') {
            return back()->with('error', "Ekspor [{$job->job_name}] gagal dibuat. Silakan coba lagi atau hubungi admin.");
        }

        return back()->with('success', "Ekspor [{$job->job_name}] siap diunduh ({$job->formatted_size}).");
    }

    /**
     * Stream the stored export file.
     */
    public function downloadExport(Request $request, int $id)
    {
        $job = $this->visibleJobs($request)->findOrFail($id);

        if ($job->status !== 'completed' || ! $job->file_path || ! Storage::disk(ExportEngineService::DISK)->exists($job->file_path)) {
            return back()->with('error', 'Berkas ekspor belum selesai, gagal dibuat, atau sudah tidak tersedia. Silakan buat ulang.');
        }

        $format = ExportEngineService::FORMATS[$job->format] ?? ExportEngineService::FORMATS['csv'];

        return Storage::disk(ExportEngineService::DISK)->download(
            $job->file_path,
            $job->job_name.'.'.$format['ext'],
            ['Content-Type' => $format['mime']]
        );
    }

    /**
     * Export history.
     */
    public function exportHistory(Request $request)
    {
        $history = $this->visibleJobs($request)->paginate(20);

        return view('content.exports.history', compact('history'));
    }

    /**
     * Admins see every job; everyone else only their own. Export files hold
     * personal data, so a guessed id must not hand someone else's file over.
     */
    private function visibleJobs(Request $request): Builder
    {
        $query = ExportJob::with('user')->orderByDesc('created_at');

        if (! in_array($request->user()->role, ['admin', 'superadmin'], true)) {
            $query->where('requested_by', $request->user()->id);
        }

        return $query;
    }
}
