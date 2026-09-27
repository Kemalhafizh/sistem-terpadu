<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\FoundationUnit;
use App\Models\StaffRotation;
use App\Models\User;
use Illuminate\Http\Request;

class StaffRotationController extends Controller
{
    public const TYPES = [
        'mutasi_tetap' => 'Mutasi tetap',
        'penugasan_sementara' => 'Penugasan sementara',
        'pertukaran' => 'Pertukaran',
        'promosi' => 'Promosi',
    ];

    public const STATUSES = [
        'proposed' => 'Diusulkan',
        'approved' => 'Disetujui',
        'active' => 'Berjalan',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public function index()
    {
        $rotations = StaffRotation::with(['user', 'fromUnit', 'toUnit'])->latest('effective_date')->get();

        return view('foundation.rotation.index', ['rotations' => $rotations, 'types' => self::TYPES, 'statuses' => self::STATUSES]);
    }

    public function create()
    {
        return view('foundation.rotation.create', [
            'units' => FoundationUnit::orderBy('unit_name')->get(),
            'staff' => User::whereNotIn('role', ['student', 'siswa', 'parent', 'orang_tua', 'alumni'])->orderBy('name')->get(['id', 'name', 'role']),
            'types' => self::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        // Used to be create($request->all()) with no validation.
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'from_unit_id' => 'required|exists:foundation_units,id',
            'to_unit_id' => 'required|exists:foundation_units,id|different:from_unit_id',
            'rotation_type' => 'required|in:'.implode(',', array_keys(self::TYPES)),
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after:effective_date',
            'reason' => 'required|string',
        ]);

        $rotation = StaffRotation::create($validated + ['status' => 'proposed']);

        return redirect()->route('foundation.rotation.show', $rotation->id)->with('success', 'Usulan rotasi staf berhasil disimpan.');
    }

    public function show($id)
    {
        return view('foundation.rotation.show', [
            'rotation' => StaffRotation::with(['user', 'fromUnit', 'toUnit', 'approver'])->findOrFail($id),
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
        ]);
    }
}
