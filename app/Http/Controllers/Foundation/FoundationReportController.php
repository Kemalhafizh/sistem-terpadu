<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\FoundationReport;
use Illuminate\Http\Request;

class FoundationReportController extends Controller
{
    public function index()
    {
        return view('foundation.reports.index', ['reports' => FoundationReport::all()]);
    }

    public function create()
    {
        return view('foundation.reports.create');
    }

    public function store(Request $request)
    {
        FoundationReport::create($request->all());

        return redirect()->route('foundation.reports.index');
    }

    public function show($id)
    {
        return view('foundation.reports.show', ['report' => FoundationReport::findOrFail($id)]);
    }
}
