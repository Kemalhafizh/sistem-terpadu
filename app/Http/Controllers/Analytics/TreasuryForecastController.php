<?php

declare(strict_types=1);

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\TreasuryForecast;
use App\Services\Analytics\DynamicTreasuryForecastingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TreasuryForecastController extends Controller
{
    public function __construct(
        protected DynamicTreasuryForecastingService $forecastService
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $scenario = $request->query('scenario', 'base');
        if (!in_array($scenario, ['conservative', 'base', 'optimistic'], true)) {
            $scenario = 'base';
        }

        $result = $this->forecastService->generateForecast($scenario);

        return response()->json([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    public function getForecasts(): JsonResponse
    {
        $forecasts = TreasuryForecast::orderBy('forecast_month')->get();

        return response()->json([
            'success' => true,
            'data' => $forecasts,
        ], 200);
    }
}
