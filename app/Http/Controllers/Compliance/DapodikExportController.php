<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\DapodikExport;
use Illuminate\Http\Request;

class DapodikExportController extends Controller
{
    public function index()
    {
        $exports = DapodikExport::with('exporter')->latest()->get();
        return view('compliance.dapodik.index', compact('exports'));
    }
}
