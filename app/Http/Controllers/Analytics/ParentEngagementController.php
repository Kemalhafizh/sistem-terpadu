<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\EngagementMetric;

class ParentEngagementController extends Controller
{
    public function dashboard()
    {
        $metrics = EngagementMetric::with('parent')->latest()->get();

        return view('analytics.engagement.dashboard', compact('metrics'));
    }

    public function show(int $id)
    {
        $metric = EngagementMetric::with('parent')->findOrFail($id);

        return view('analytics.engagement.detail', compact('metric'));
    }
}
