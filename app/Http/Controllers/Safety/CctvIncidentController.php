<?php

namespace App\Http\Controllers\Safety;

use App\Http\Controllers\Controller;

class CctvIncidentController extends Controller
{
    public function index()
    {
        return view('fase60.surveillance.dashboard');
    }

    public function show($id)
    {
        return view('fase60.surveillance.incident-detail');
    }

    public function dashboardStats()
    {
        return response()->json();
    }
}
