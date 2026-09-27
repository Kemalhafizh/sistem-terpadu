<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CustomReportExport;
use App\Http\Controllers\Controller;
use App\Models\CustomReport;
use App\Services\ReportBuilderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportBuilderController extends Controller
{
    protected $service;

    public function __construct(ReportBuilderService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $user = auth()->user();
        $query = CustomReport::with('creator');

        // Non-admin can only see shared templates or their own created templates
        if ($user->role !== 'admin' && $user->role !== 'superadmin') {
            $query->where(function ($q) use ($user) {
                $q->where('is_shared', true)
                    ->orWhere('created_by', $user->id);
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        return view('admin.report-builder.index', compact('reports'));
    }

    public function create()
    {
        $sources = $this->service->getAvailableSources();

        return view('admin.report-builder.create', compact('sources'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source' => 'required|string',
            'columns' => 'required|array',
            'filters' => 'nullable|array',
            'groupBy' => 'nullable|string',
            'orderBy' => 'nullable|string',
            'orderDir' => 'nullable|in:asc,desc',
            'chartType' => 'nullable|in:table,line,bar,pie,donut',
        ]);

        $config = [
            'source' => $request->source,
            'columns' => $request->columns,
            'filters' => $request->filters ?? [],
            'groupBy' => $request->groupBy,
            'orderBy' => $request->orderBy,
            'orderDir' => $request->orderDir ?? 'asc',
            'chartType' => $request->chartType ?? 'table',
        ];

        CustomReport::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id(),
            'config' => $config,
            'is_shared' => $request->has('is_shared'),
        ]);

        return redirect()->route('admin.report-builder.index')
            ->with('success', 'Template laporan kustom berhasil disimpan.');
    }

    public function show(CustomReport $report)
    {
        $user = auth()->user();

        // Guard access
        if ($user->role !== 'admin' && $user->role !== 'superadmin' && ! $report->is_shared && $report->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        try {
            $data = $this->service->executeQuery($report->config);
            $sources = $this->service->getAvailableSources();
            $sourceConfig = $sources[$report->config['source']];

            $headings = [];
            foreach ($report->config['columns'] as $col) {
                $headings[$col] = $sourceConfig['columns'][$col]['label'] ?? $col;
            }

            return view('admin.report-builder.show', compact('report', 'data', 'headings', 'sources'));
        } catch (\Exception $e) {
            return redirect()->route('admin.report-builder.index')
                ->with('error', 'Gagal menjalankan laporan: '.$e->getMessage());
        }
    }

    public function preview(Request $request)
    {
        $request->validate([
            'source' => 'required|string',
            'columns' => 'required|array',
            'filters' => 'nullable|array',
        ]);

        try {
            $config = [
                'source' => $request->source,
                'columns' => $request->columns,
                'filters' => $request->filters ?? [],
                'orderBy' => $request->orderBy,
                'orderDir' => $request->orderDir ?? 'asc',
            ];

            // Limit preview to 10 rows for snappy dynamic render
            $data = $this->service->executeQuery($config)->take(10);

            $sources = $this->service->getAvailableSources();
            $sourceConfig = $sources[$request->source];

            $headings = [];
            foreach ($request->columns as $col) {
                $headings[$col] = $sourceConfig['columns'][$col]['label'] ?? $col;
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'headings' => $headings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function exportPdf(CustomReport $report)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && $user->role !== 'superadmin' && ! $report->is_shared && $report->created_by !== $user->id) {
            abort(403);
        }

        $data = $this->service->executeQuery($report->config);
        $sources = $this->service->getAvailableSources();
        $sourceConfig = $sources[$report->config['source']];

        $headings = [];
        foreach ($report->config['columns'] as $col) {
            $headings[$col] = $sourceConfig['columns'][$col]['label'] ?? $col;
        }

        $pdf = Pdf::loadView('admin.report-builder.pdf', compact('report', 'data', 'headings'));

        return $pdf->setPaper('a4', 'landscape')
            ->download('Laporan_'.str_replace(' ', '_', $report->name).'.pdf');
    }

    public function exportExcel(CustomReport $report)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && $user->role !== 'superadmin' && ! $report->is_shared && $report->created_by !== $user->id) {
            abort(403);
        }

        $data = $this->service->executeQuery($report->config);
        $sources = $this->service->getAvailableSources();
        $sourceConfig = $sources[$report->config['source']];

        $headings = [];
        foreach ($report->config['columns'] as $col) {
            $headings[] = $sourceConfig['columns'][$col]['label'] ?? $col;
        }

        return Excel::download(
            new CustomReportExport($data, $headings, $report->config['columns']),
            'Laporan_'.str_replace(' ', '_', $report->name).'.xlsx'
        );
    }

    public function destroy(CustomReport $report)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && $user->role !== 'superadmin' && $report->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus laporan ini.');
        }

        $report->delete();

        return redirect()->route('admin.report-builder.index')
            ->with('success', 'Template laporan kustom berhasil dihapus.');
    }
}
