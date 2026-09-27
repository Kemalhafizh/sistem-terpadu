<?php

namespace App\Http\Controllers\UX;

use App\Http\Controllers\Controller;
use App\Services\UX\PrintStylesheetService;
use Illuminate\Http\Request;

class PrintEngineController extends Controller
{
    public function preview(Request $request, PrintStylesheetService $service)
    {
        $layout = $service->generatePrintLayout('Laporan Hasil Pembelajaran Rapor Siswa', 'A4', 'portrait');

        return view('print.preview', compact('layout'));
    }
}
