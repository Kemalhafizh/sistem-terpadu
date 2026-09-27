<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\KeyPerformanceIndicator;
use App\Models\KpiMeasurement;
use Illuminate\Http\Request;

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

        $validated['kpi_id'] = $kpi->id;
        $validated['recorded_by'] = auth()->id() ?? 1;

        KpiMeasurement::create($validated);
        $kpi->update(['current_value' => $request->value]);

        return redirect()->back()->with('success', 'Pengukuran KPI berhasil disimpan.');
    }

    public function report($id)
    {
        $kpi = KeyPerformanceIndicator::with('measurements')->findOrFail($id);

        return view('strategic.kpi.report', compact('kpi'));
    }
}
