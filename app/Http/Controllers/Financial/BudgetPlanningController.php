<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\BudgetLineItem;
use App\Models\BudgetPlan;
use App\Services\Financial\BudgetPlanningService;
use DomainException;
use Illuminate\Http\Request;

/**
 * RKAS / RAPBS. TU drafts (BudgetPlan::PREPARER_ROLES), the principal
 * approves (APPROVER_ROLES); the routes carry those roles.
 */
class BudgetPlanningController extends Controller
{
    public function dashboard(BudgetPlanningService $service)
    {
        $plans = BudgetPlan::with(['creator', 'approver'])->latest()->get();
        $activePlan = BudgetPlan::where('status', 'approved')->latest()->first() ?? $plans->first();
        $report = $activePlan ? $service->getVarianceReport($activePlan) : null;

        return view('budget.dashboard', compact('plans', 'activePlan', 'report'));
    }

    public function create()
    {
        return view('budget.create');
    }

    public function store(Request $request, BudgetPlanningService $service)
    {
        $validated = $request->validate([
            'fiscal_year' => ['required', 'string', 'regex:/^\d{4}(\/\d{4})?$/'],
            'title' => 'required|string|max:255',
        ]);

        $plan = $service->createPlan($validated, auth()->id());

        return redirect()->route('financial.budget.show', $plan->id)
            ->with('success', __('RKAS dibuat. Tambahkan pos anggarannya, lalu ajukan ke Kepala Sekolah.'));
    }

    public function show(int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::with(['revisions.reviser', 'creator', 'approver'])->findOrFail($id);
        $report = $service->getVarianceReport($plan);

        return view('budget.line-items', compact('plan', 'report'));
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
            'quarter' => 'required|in:Q1,Q2,Q3,Q4,annual',
            'notes' => 'nullable|string',
        ]);

        return $this->attempt(fn () => $service->addLineItem($plan, $validated), __('Pos anggaran ditambahkan.'));
    }

    public function recordRealization(Request $request, int $id, BudgetLineItem $item, BudgetPlanningService $service)
    {
        abort_unless((int) $item->budget_plan_id === $id, 404);

        $validated = $request->validate(['actual_amount' => 'nullable|numeric|min:0']);
        $actual = isset($validated['actual_amount']) ? (float) $validated['actual_amount'] : null;

        return $this->attempt(fn () => $service->recordRealization($item, $actual), __('Realisasi pos anggaran disimpan.'));
    }

    public function submitApproval(int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::findOrFail($id);

        return $this->attempt(fn () => $service->submitForApproval($plan), __('RKAS diajukan ke Kepala Sekolah.'));
    }

    public function approve(int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::findOrFail($id);

        return $this->attempt(fn () => $service->approvePlan($plan, auth()->id()), __('RKAS disetujui.'));
    }

    public function varianceReport(int $id, BudgetPlanningService $service)
    {
        $plan = BudgetPlan::findOrFail($id);
        $report = $service->getVarianceReport($plan);

        return view('budget.variance-report', compact('plan', 'report'));
    }

    private function attempt(callable $action, string $success)
    {
        try {
            $action();
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $success);
    }
}
