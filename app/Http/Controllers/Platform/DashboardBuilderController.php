<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\CustomDashboard;
use Illuminate\Http\Request;

class DashboardBuilderController extends Controller
{
    public function index()
    {
        $dashboards = CustomDashboard::with('widgets')->latest()->get();
        return view('developer.dashboards.index', compact('dashboards'));
    }
}
