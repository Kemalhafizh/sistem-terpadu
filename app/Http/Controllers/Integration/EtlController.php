<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\EtlJob;
use App\Services\Integration\EtlPipelineService;
use Illuminate\Http\Request;

class EtlController extends Controller
{
    /**
     * Display ETL Jobs List & History.
     */
    public function index()
    {
        $jobs = EtlJob::with('creator')->orderBy('id', 'desc')->get();

        return view('integration.etl.index', compact('jobs'));
    }

    /**
     * Create new ETL migration job.
     */
    public function createJob(Request $request, EtlPipelineService $etlService)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'source_type' => 'required|in:dapodik_xml,emis_json,excel,csv',
            'source_file_path' => 'required|string',
            'target_table' => 'required|string',
        ]);

        $job = $etlService->createJob($request->all(), auth()->id());

        return back()->with('success', "Pekerjaan ETL Migrasi [{$job->name}] berhasil dikonfigurasikan!");
    }

    /**
     * Display Visual Column Mapper.
     */
    public function configureMapping(int $id)
    {
        $job = EtlJob::findOrFail($id);

        return view('integration.etl.configure-mapping', compact('job'));
    }

    /**
     * Execute ETL migration pipeline.
     */
    public function execute(int $id, EtlPipelineService $etlService)
    {
        $job = $etlService->execute($id);

        return redirect()->route('etl.progress', $job->id)
            ->with('success', "Proses migrasi ETL [{$job->name}] selesai dijalankan!");
    }

    /**
     * View real-time ETL progress bar.
     */
    public function progress(int $id)
    {
        $job = EtlJob::findOrFail($id);

        return view('integration.etl.progress', compact('job'));
    }

    /**
     * Rollback ETL migration job.
     */
    public function rollback(int $id, EtlPipelineService $etlService)
    {
        $job = $etlService->rollback($id);

        return back()->with('success', "Rollback data ETL #{$job->id} berhasil dilakukan.");
    }
}
