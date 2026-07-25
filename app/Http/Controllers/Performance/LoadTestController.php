<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\LoadTestResult;
use App\Services\Performance\LoadTestService;
use Illuminate\Http\Request;

class LoadTestController extends Controller
{
    /**
     * Display Load Testing Dashboard.
     */
    public function dashboard()
    {
        $tests = LoadTestResult::orderBy('id', 'desc')->get();

        return view('performance.load-test-dashboard', compact('tests'));
    }

    /**
     * Run load testing scenario.
     */
    public function runTest(Request $request, LoadTestService $loadService)
    {
        $request->validate([
            'scenario_name' => 'required|string|max:100',
            'concurrent_users' => 'required|integer|min:10|max:1000',
            'duration_seconds' => 'required|integer|min:5|max:300',
        ]);

        $test = $loadService->runScenario(
            $request->scenario_name,
            $request->concurrent_users,
            $request->duration_seconds
        );

        return redirect()->route('load_test.results', $test->id)
            ->with('success', "Load test skenario [{$test->scenario_name}] selesai dengan throughput {$test->requests_per_second} Req/Sec!");
    }

    /**
     * Display Load Test Detail Results.
     */
    public function results(int $id)
    {
        $test = LoadTestResult::findOrFail($id);

        return view('performance.results', compact('test'));
    }

    /**
     * Display Comparison view.
     */
    public function compareBaseline(int $id, LoadTestService $loadService)
    {
        $comparison = $loadService->compareResults($id);

        return view('performance.comparison', compact('comparison'));
    }
}
