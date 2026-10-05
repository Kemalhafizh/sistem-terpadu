<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;
use App\Models\WasteLog;
use App\Support\Access\RouteAccess;
use Illuminate\Http\Request;

class WasteManagementController extends Controller
{
    public const DISPOSAL_METHODS = ['recycled', 'composted', 'landfill', 'bank_sampah'];

    public function index(Request $request)
    {
        $logs = WasteLog::with('recorder.user')->latest('log_date')->latest('id')->take(30)->get();

        return view('fase59.waste.tracker', [
            'logs' => $logs,
            'methods' => self::DISPOSAL_METHODS,
            'canRecord' => RouteAccess::allowsRoute($request->user(), 'waste.store'),
        ]);
    }

    /**
     * Record a waste log. It used to answer "Log sampah tersimpan." without
     * saving anything (FASE 118.3).
     */
    public function storeLog(Request $request)
    {
        $validated = $request->validate([
            'log_date' => 'required|date|before_or_equal:today',
            'waste_category' => 'required|string|max:100',
            'weight_kg' => 'required|numeric|gt:0|max:999999',
            'disposal_method' => 'required|in:'.implode(',', self::DISPOSAL_METHODS),
            'revenue_generated' => 'nullable|numeric|min:0',
        ]);

        // waste_logs.recorded_by references employees: the log is signed by
        // the employee record of the signed-in account.
        $employeeId = $request->user()->employee?->id;
        if (! $employeeId) {
            return back()->withInput()->withErrors(['log_date' => __('Akun ini belum terhubung dengan data pegawai, jadi log sampah belum bisa dicatat atas nama Anda.')]);
        }

        WasteLog::create(['revenue_generated' => $validated['revenue_generated'] ?? 0, 'recorded_by' => $employeeId] + $validated);

        return redirect()->route('waste.tracker')->with('success', __('Log sampah tersimpan.'));
    }

    public function bankSampahSync()
    {
        return view('fase59.waste.bank-sampah');
    }
}
