<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\UnitAccreditationRecord;
use Illuminate\Http\Request;

class AccreditationTrackerController extends Controller
{
    public function index()
    {
        return view('foundation.accreditation.index', ['records' => UnitAccreditationRecord::all()]);
    }

    public function create()
    {
        return view('foundation.accreditation.create');
    }

    public function store(Request $request)
    {
        UnitAccreditationRecord::create($request->all());

        return redirect()->route('foundation.accreditation.index');
    }

    public function show($id)
    {
        return view('foundation.accreditation.detail', ['record' => UnitAccreditationRecord::findOrFail($id)]);
    }
}
