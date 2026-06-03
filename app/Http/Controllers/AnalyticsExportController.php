<?php

namespace App\Http\Controllers;

use App\Exports\AnalyticsExport;
use App\Services\AnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AnalyticsExportController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function exportAttendancePdf()
    {
        $attendanceTrend = $this->analyticsService->getAttendanceTrend();
        $pdf = Pdf::loadView('analytics.pdf.attendance', compact('attendanceTrend'));

        return $pdf->download('laporan_analitik_kehadiran.pdf');
    }

    public function exportGradesPdf()
    {
        $grades = $this->analyticsService->getGradeDistribution();
        $pdf = Pdf::loadView('analytics.pdf.grades', compact('grades'));

        return $pdf->download('laporan_analitik_akademik_nilai.pdf');
    }

    public function exportFinancePdf()
    {
        $finance = $this->analyticsService->getFinancialOverview();
        $pdf = Pdf::loadView('analytics.pdf.finance', compact('finance'));

        return $pdf->download('laporan_analitik_keuangan.pdf');
    }

    public function exportAllExcel()
    {
        return Excel::download(new AnalyticsExport($this->analyticsService), 'laporan_analitik_sekolah_lengkap.xlsx');
    }
}
