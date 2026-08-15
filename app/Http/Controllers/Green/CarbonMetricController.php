<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CarbonMetricController extends Controller
{
    public function index()
    {
        return view('fase59.carbon.dashboard');
    }

    public function storeDailyData(Request $request)
    {
        // Implementation
        return redirect()->back()->with('success', 'Data tersimpan.');
    }

    public function calculateMonthly()
    {
        // Implementation
    }

    public function reports()
    {
        return view('fase59.carbon.reports');
    }
}
