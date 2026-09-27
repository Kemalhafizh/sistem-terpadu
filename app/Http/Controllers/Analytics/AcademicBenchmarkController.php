<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AcademicBenchmark;

class AcademicBenchmarkController extends Controller
{
    public function dashboard()
    {
        $benchmarks = AcademicBenchmark::with('subject')->latest()->get();

        return view('analytics.benchmarking.dashboard', compact('benchmarks'));
    }

    public function importForm()
    {
        return view('analytics.benchmarking.import');
    }
}
