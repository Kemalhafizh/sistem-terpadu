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
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function reports()
    {
        return view('fase59.carbon.reports');
    }
}
