<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\FoundationUnit;
use App\Models\UnitAccreditationRecord;
use Illuminate\Http\Request;

class AccreditationTrackerController extends Controller
{
    public const STATUSES = [
        'active' => 'Berlaku',
        'renewal_in_progress' => 'Proses reakreditasi',
        'expired' => 'Kedaluwarsa',
    ];

    public function index()
    {
        $records = UnitAccreditationRecord::with('unit')->orderBy('valid_until')->get();

        return view('foundation.accreditation.index', ['records' => $records, 'statuses' => self::STATUSES]);
    }

    public function create()
    {
        return view('foundation.accreditation.create', [
            'units' => FoundationUnit::orderBy('unit_name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        // Used to be create($request->all()) with no validation.
        $validated = $request->validate([
            'foundation_unit_id' => 'required|exists:foundation_units,id',
            'accreditation_body' => 'required|string|max:255',
            'accreditation_grade' => 'required|string|max:20',
            'accreditation_score' => 'nullable|numeric|min:0|max:100',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'certificate_number' => 'nullable|string|max:255',
            'status' => 'required|in:'.implode(',', array_keys(self::STATUSES)),
        ]);

        $record = UnitAccreditationRecord::create($validated);

        return redirect()->route('foundation.accreditation.show', $record->id)->with('success', 'Data akreditasi berhasil disimpan.');
    }

    public function show($id)
    {
        return view('foundation.accreditation.detail', [
            'record' => UnitAccreditationRecord::with('unit')->findOrFail($id),
            'statuses' => self::STATUSES,
        ]);
    }
}
