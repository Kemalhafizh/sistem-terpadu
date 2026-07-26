<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\BudgetPlan;
use App\Services\Financial\BudgetPlanningService;
use Illuminate\Http\Request;

class BudgetPlanningController extends Controller
{
    public function dashboard(BudgetPlanningService $service)
    {
        $plans = BudgetPlan::with(['creator', 'approver'])->latest()->get();
        $activePlan = BudgetPlan::with('lineItems')->where('status', 'approved')->latest()->first() ?? $plans->first();

        $report = $activePlan ? $service->getVarianceReport($activePlan) : null;
        $forecast = $activePlan ? $service->forecastNextYear($activePlan) : null;

        return view('budget.dashboard', compact('plans', 'activePlan', 'report', 'forecast'));
    }

    public function create()
    {
        return view('budget.create');
    }

    public function store(Request $request, BudgetPlanningService $service)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|string|max:9',
            'title' => 'required|string|max:255',
            'total_revenue_planned' => 'nullable|numeric|min:0',
            'total_expense_planned' => 'nullable|numeric|min:0',
        ]);

        $plan = $service->createPlan($validated, auth()->id());

        return redirect()->route('financial.budget.show', $plan->id)
            ->with('success', 'Rencana Anggaran (RAPBS) berhasil dibuat!');
    }

    public function show(int $id)
    {
        $plan = BudgetPlan::with(['lineItems', 'revisions.reviser', 'creator', 'approver'])->findOrFail($id);
        return view('budget.line-items', compact('plan'));
    }

    public function storeLineItem(Request $request, int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|in:revenue,expense',
            'sub_category' => 'required|string|max:100',
            'account_code' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'planned_amount' => 'required|numeric|min:0',
            'actual_amount' => 'nullable|numeric|min:0',
            'quarter' => 'required|in:Q1,Q2,Q3,Q4,annual',
            'notes' => 'nullable|string',
        ]);

        $service->addLineItem($plan, $validated);

        return back()->with('success', 'Item anggaran berhasil ditambahkan!');
    }

    public function submitApproval(int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::findOrFail($id);
        $service->submitForApproval($plan);

        return back()->with('success', 'RAPBS telah diajukan untuk persetujuan!');
    }

    public function approve(int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::findOrFail($id);
        $service->approvePlan($plan, auth()->id());

        return back()->with('success', 'RAPBS telah disetujui!');
    }

    public function varianceReport(int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::with('lineItems')->findOrFail($id);
        $report = $service->getVarianceReport($plan);

        return view('budget.variance-report', compact('plan', 'report'));
    }

    public function forecastReport(int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::findOrFail($id);
        $forecast = $service->forecastNextYear($plan);

        return view('budget.forecast', compact('plan', 'forecast'));
    }
}
