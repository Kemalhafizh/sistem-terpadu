<?php

namespace App\Http\Controllers\Crisis;

use App\Http\Controllers\Controller;
use App\Models\BullyingReport;
use App\Services\Crisis\AntiBullyingService;
use Illuminate\Http\Request;

class AntiBullyingController extends Controller
{
    protected $service;

    public function __construct(AntiBullyingService $service)
    {
        $this->service = $service;
    }

    public function reportForm()
    {
        return view('crisis.bullying.report-form');
    }

    public function storeReport(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'type' => 'required|string',
            'severity' => 'required|string',
            'is_anonymous' => 'boolean',
        ]);

        $this->service->submitReport($validated);

        return redirect()->route('crisis.bullying.report')->with('success', 'Laporan berhasil dikirim secara rahasia.');
    }

    public function index()
    {
        $reports = BullyingReport::latest()->get();

        return view('crisis.bullying.index', compact('reports'));
    }

    public function show($id)
    {
        $report = BullyingReport::findOrFail($id);

        return view('crisis.bullying.show', compact('report'));
    }

    public function statistics()
    {
        $stats = $this->service->getStatsByType();

        return view('crisis.bullying.statistics', compact('stats'));
    }
}
