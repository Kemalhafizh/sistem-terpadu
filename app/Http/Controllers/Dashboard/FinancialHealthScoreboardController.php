<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\FinancialScoreboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialHealthScoreboardController extends Controller
{
    public function __construct(
        protected FinancialScoreboardService $financialService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $metric = $this->financialService->computeFinancialHealth();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'metric' => $metric]);
        }

        return view('advanced-dashboards.financial-health-scoreboard', compact('metric'));
    }
}
