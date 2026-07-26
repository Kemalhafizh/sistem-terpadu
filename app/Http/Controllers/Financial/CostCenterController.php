<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\User;
use App\Services\Financial\CostCenterService;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    public function dashboard(CostCenterService $service)
    {
        $costCenters = CostCenter::with(['head', 'transactions.recorder'])->get();

        $utilizationData = $costCenters->map(function ($cc) use ($service) {
            return $service->getUtilization($cc);
        });

        return view('cost-center.dashboard', compact('costCenters', 'utilizationData'));
    }

    public function allocateForm()
    {
        $costCenters = CostCenter::all();
        $heads = User::whereIn('role', ['admin', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'staf_tu'])->get();

        return view('cost-center.allocate', compact('costCenters', 'heads'));
    }

    public function storeCostCenter(Request $request, CostCenterService $service)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:cost_centers,code',
            'name' => 'required|string|max:255',
            'department' => 'required|in:kurikulum,kesiswaan,humas,sarpras,tu,keuangan,keislaman',
            'head_id' => 'nullable|exists:users,id',
            'annual_budget_allocation' => 'required|numeric|min:0',
        ]);

        $cc = $service->createCostCenter($validated);

        return redirect()->route('financial.cost-centers.dashboard')
            ->with('success', "Cost Center [{$cc->code} - {$cc->name}] berhasil dibuat!");
    }

    public function storeAllocation(int $id, Request $request, CostCenterService $service)
    {
        $cc = CostCenter::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string|max:255',
        ]);

        $service->allocateBudget($cc, $validated['amount'], $validated['description'], auth()->id());

        return back()->with('success', 'Alokasi anggaran tambahan berhasil dicatat!');
    }

    public function storeExpense(int $id, Request $request, CostCenterService $service)
    {
        $cc = CostCenter::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string|max:255',
        ]);

        $service->recordExpense($cc, $validated['amount'], $validated['description'], auth()->id());

        return back()->with('success', 'Pengeluaran Cost Center berhasil dicatat!');
    }

    public function transactions(int $id)
    {
        $costCenter = CostCenter::with(['transactions.recorder', 'head'])->findOrFail($id);
        return view('cost-center.transactions', compact('costCenter'));
    }
}
