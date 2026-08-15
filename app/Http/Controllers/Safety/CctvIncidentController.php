<?php

namespace App\Http\Controllers\Safety;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CctvIncidentController extends Controller
{
    public function index() { return view('fase60.surveillance.dashboard'); }
    public function show($id) { return view('fase60.surveillance.incident-detail'); }
    public function updateStatus(Request $request, $id) { return redirect()->back(); }
    public function resolveIncident(Request $request, $id) { return redirect()->back(); }
    public function dashboardStats() { return response()->json(); }
}
