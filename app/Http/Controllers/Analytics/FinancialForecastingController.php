<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\FinancialForecast;
use App\Services\Analytics\FinancialForecastingService;
use Illuminate\Http\Request;

class FinancialForecastingController extends Controller
{
    public function dashboard()
    {
        $forecasts = FinancialForecast::with('creator')->latest()->get();
        return view('analytics.finance.forecast', compact('forecasts'));
    }

    public function createScenario()
    {
        return view('analytics.finance.scenario-builder');
    }

    public function storeScenario(Request $request, FinancialForecastingService $service)
    {
        $validated = $request->validate([
            'scenario_name' => 'required|string',
            'projected_revenue' => 'required|numeric',
            'projected_expense' => 'required|numeric',
        ]);

        $service->runSimulation($validated['scenario_name'], $validated['projected_revenue'], $validated['projected_expense'], auth()->id());

        return redirect()->route('analytics.finance.dashboard')->with('success', 'Skenario proyeksi arus kas berhasil dibuat!');
    }
}
