<?php

namespace App\Http\Controllers\HumanCapital;

use App\Http\Controllers\Controller;
use App\Models\CpdActivity;
use App\Models\CpdCategory;
use App\Models\CpdTarget;
use App\Services\HumanCapital\CpdTrackerService;
use Illuminate\Http\Request;

class CpdTrackerController extends Controller
{
    public function dashboard()
    {
        $employeeId = auth()->id();
        $target = CpdTarget::where('employee_id', $employeeId)->where('academic_year', '2026/2027')->first();
        $activities = CpdActivity::with('category')->where('employee_id', $employeeId)->latest()->get();
        $categories = CpdCategory::all();

        return view('cpd.dashboard', compact('target', 'activities', 'categories'));
    }

    public function logActivity(Request $request, CpdTrackerService $service)
    {
        $validated = $request->validate([
            'cpd_category_id' => 'required|exists:cpd_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'hours' => 'required|integer|min:1',
            'credits_earned' => 'required|numeric|min:0.5',
        ]);

        $service->logActivity($validated, auth()->id());

        return back()->with('success', 'Aktivitas Pengembangan Keprofesian Berkelanjutan (PKB/CPD) berhasil dicatat!');
    }

    public function teamOverview()
    {
        $activities = CpdActivity::with(['employee', 'category'])->where('status', 'pending')->get();

        return view('cpd.team-overview', compact('activities'));
    }

    public function verifyActivity(int $id, Request $request, CpdTrackerService $service)
    {
        $activity = CpdActivity::findOrFail($id);
        $service->verifyActivity($activity, auth()->id(), true);

        return back()->with('success', 'Kredit PKB/CPD guru telah diverifikasi & ditambahkan!');
    }
}
