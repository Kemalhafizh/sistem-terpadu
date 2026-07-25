<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\SystemScalingConfig;
use App\Services\Performance\ScalingConfigService;

class ScalingController extends Controller
{
    /**
     * Display Horizontal Scaling Architecture Blueprint Dashboard.
     */
    public function dashboard(ScalingConfigService $scalingService)
    {
        $replicaConfig = $scalingService->getReadReplicaConfig();
        $sessionConfig = $scalingService->getSessionConfig();
        $health = $scalingService->healthCheck();
        $configs = SystemScalingConfig::all();

        return view('performance.scaling-dashboard', compact('replicaConfig', 'sessionConfig', 'health', 'configs'));
    }

    /**
     * Display Configuration Panel.
     */
    public function configuration()
    {
        $configs = SystemScalingConfig::all();

        return view('performance.configuration', compact('configs'));
    }
}
