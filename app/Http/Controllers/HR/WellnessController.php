<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;

class WellnessController extends Controller
{
    public function index()
    {
        return view('wellness.catalog');
    }

    public function myWellness()
    {
        return view('wellness.employee-dashboard');
    }

    public function hrAnalytics()
    {
        return view('wellness.hr-analytics');
    }
}
