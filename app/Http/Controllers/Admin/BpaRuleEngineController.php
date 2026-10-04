<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BpaBusinessRule;
use App\Models\BpaRuleExecution;
use App\Services\BusinessRuleEngineService;
use Illuminate\Http\Request;

class BpaRuleEngineController extends Controller
{
    /**
     * Display Business Process Automation Console.
     */
    public function index()
    {
        $rules = BpaBusinessRule::withCount('executions')->orderBy('created_at', 'desc')->get();
        $recentExecutions = BpaRuleExecution::with('rule')->orderBy('created_at', 'desc')->take(20)->get();

        $totalRules = BpaBusinessRule::count();
        $activeRules = BpaBusinessRule::where('is_active', true)->count();
        $totalExecutions = BpaRuleExecution::count();
        $matchedExecutions = BpaRuleExecution::where('matched', true)->count();

        $matchRate = $totalExecutions > 0 ? round(($matchedExecutions / $totalExecutions) * 100, 1) : 100.0;

        return view('admin.system.bpa_rules', compact(
            'rules',
            'recentExecutions',
            'totalRules',
            'activeRules',
            'totalExecutions',
            'matchedExecutions',
            'matchRate'
        ));
    }

    /**
     * Store new business automation rule.
     */
    public function store(Request $request, BusinessRuleEngineService $ruleEngine)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'trigger_event' => 'required|string|max:100',
            'conditions' => 'required|array|min:1',
            'actions' => 'required|array|min:1',
        ]);

        $rule = $ruleEngine->createRule([
            'name' => $request->name,
            'trigger_event' => $request->trigger_event,
            'conditions' => $request->conditions,
            'actions' => $request->actions,
            'is_active' => true,
        ]);

        return back()->with('success', "Aturan Otomatisasi Bisnis [{$rule->name}] berhasil dibuat!");
    }

    /**
     * Toggle rule active status.
     */
    public function toggle(BpaBusinessRule $rule)
    {
        $rule->is_active = ! $rule->is_active;
        $rule->save();

        $statusLabel = $rule->is_active ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';

        return back()->with('success', "Status Aturan Bisnis [{$rule->name}] berhasil {$statusLabel}.");
    }

    /**
     * Delete business rule.
     */
    public function destroy(BpaBusinessRule $rule)
    {
        $name = $rule->name;
        $rule->delete();

        return back()->with('success', "Aturan Bisnis [{$name}] telah dihapus.");
    }

    /**
     * Test execute rule simulation.
     */
    public function testExecute(Request $request, BusinessRuleEngineService $ruleEngine)
    {
        $triggerEvent = $request->input('trigger_event', 'attendance.checked');
        $context = [
            'attendance_percentage' => 75.0,
            'tahfidz_status' => 'Behind',
            'employee_lateness_count' => 4,
            'student_penalty_points' => 65,
        ];

        $res = $ruleEngine->evaluateAndExecute($triggerEvent, $context);

        $matched = collect($res['executions'])->where('matched', true)->count();
        $message = __('Simulasi aturan [:event] selesai. Aturan dievaluasi: :evaluated, cocok: :matched.', [
            'event' => $triggerEvent, 'evaluated' => $res['rules_evaluated'], 'matched' => $matched,
        ]);
        if ($matched > 0) {
            $message .= ' '.__('Aksinya belum dijalankan karena belum ada pelaksana (tiket BK, WhatsApp, potongan gaji, SP1).');
        }

        return back()->with('success', $message);
    }
}
