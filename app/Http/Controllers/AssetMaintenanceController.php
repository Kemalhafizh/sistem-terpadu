<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Http\Request;

class AssetMaintenanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin', 'waka_sarpras', 'kepala_sekolah'])) {
            abort(403);
        }

        $maintenances = AssetMaintenance::with(['asset', 'handler'])->latest()->paginate(15);
        $assets = Asset::all();

        return view('assets.maintenances.index', compact('maintenances', 'assets'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin', 'waka_sarpras'])) {
            abort(403);
        }

        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_date' => 'required|date',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'type' => 'required|in:preventif,korektif,upgrade',
            'description' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'vendor' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed',
        ]);

        AssetMaintenance::create(array_merge($validated, [
            'handled_by' => auth()->id(),
        ]));

        return back()->with('success', 'Jadwal perawatan berhasil disimpan.');
    }

    public function update(Request $request, AssetMaintenance $maintenance)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin', 'waka_sarpras'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed',
            'next_maintenance_date' => 'nullable|date',
            'cost' => 'required|numeric|min:0',
            'description' => 'required|string',
        ]);

        $maintenance->update($validated);

        return back()->with('success', 'Data perawatan berhasil diperbarui.');
    }
}
