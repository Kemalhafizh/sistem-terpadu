<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use App\Services\FeatureFlagService;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    /**
     * Display Zero-Downtime Feature Flag Engine Control Panel.
     */
    public function index()
    {
        $flags = FeatureFlag::query()->orderBy('name', 'asc')->get();

        return view('admin.system.feature_flags', compact('flags'));
    }

    /**
     * Toggle active status of feature flag.
     */
    public function toggle(Request $request, FeatureFlag $flag, FeatureFlagService $flagService)
    {
        $newState = $flagService->toggle($flag->key);

        $statusText = $newState ? 'DIAKTIFKAN (ON)' : 'DINONAKTIFKAN (OFF)';

        return back()->with('success', "Status Feature Flag [{$flag->name}] berhasil diubah menjadi {$statusText}!");
    }

    /**
     * Update percentage rollout & target roles.
     */
    public function update(Request $request, FeatureFlag $flag, FeatureFlagService $flagService)
    {
        $request->validate([
            'rollout_percentage' => 'required|integer|min:0|max:100',
            'target_roles' => 'nullable|array',
        ]);

        $flagService->updateFlag(
            $flag->key,
            (int) $request->rollout_percentage,
            $request->target_roles
        );

        return back()->with('success', "Konfigurasi Rollout Feature Flag [{$flag->name}] berhasil diperbarui!");
    }
}
