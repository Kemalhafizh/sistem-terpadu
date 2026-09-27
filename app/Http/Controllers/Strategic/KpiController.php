<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\KeyPerformanceIndicator;
use App\Models\KpiMeasurement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KpiController extends Controller
{
    public function storeMeasurement(Request $request, $id)
    {
        $kpi = KeyPerformanceIndicator::findOrFail($id);
        $validated = $request->validate([
            'measurement_date' => 'required|date',
            'value' => 'required|numeric',
            'evidence' => 'nullable|string',
            'analysis' => 'nullable|string',
        ]);

        // kpi_measurements has a unique (kpi_id, measurement_date) index: a
        // second entry for the same date used to be a 500. (whereDate, since
        // the date cast may store a time part depending on the driver.)
        if ($kpi->measurements()->whereDate('measurement_date', $validated['measurement_date'])->exists()) {
            throw ValidationException::withMessages(['measurement_date' => 'Sudah ada pengukuran untuk tanggal ini.']);
        }

        $validated['kpi_id'] = $kpi->id;
        $validated['recorded_by'] = auth()->id();

        KpiMeasurement::create($validated);

        // The latest measurement (by date) is the KPI's current value.
        $latest = $kpi->measurements()->orderByDesc('measurement_date')->value('value');
        $kpi->update(['current_value' => $latest]);

        return redirect()->back()->with('success', 'Pengukuran KPI berhasil disimpan.');
    }

    public function report($id)
    {
        $kpi = KeyPerformanceIndicator::with(['objective.plan', 'measurements' => fn ($q) => $q->orderBy('measurement_date')])->findOrFail($id);

        return view('strategic.kpi.report', compact('kpi'));
    }
}
