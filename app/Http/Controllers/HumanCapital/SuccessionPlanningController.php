<?php

namespace App\Http\Controllers\HumanCapital;

use App\Http\Controllers\Controller;
use App\Models\LeadershipPosition;
use App\Models\SuccessionCandidate;
use App\Models\User;
use App\Services\HumanCapital\SuccessionPlanningService;
use Illuminate\Http\Request;

class SuccessionPlanningController extends Controller
{
    public function positions()
    {
        $positions = LeadershipPosition::with(['currentHolder', 'candidates.candidate'])->get();
        $teachers = User::where('role', 'guru')->get();

        return view('succession.positions', compact('positions', 'teachers'));
    }

    public function storeCandidate(Request $request, SuccessionPlanningService $service)
    {
        $validated = $request->validate([
            'leadership_position_id' => 'required|exists:leadership_positions,id',
            'candidate_id' => 'required|exists:users,id',
            'readiness_level' => 'required|in:ready_now,ready_1yr,ready_3yr,developing',
        ]);

        $position = LeadershipPosition::findOrFail($validated['leadership_position_id']);
        $service->addCandidate(
            $position,
            $validated['candidate_id'],
            $validated['readiness_level'],
            ['Kepemimpinan Teruji', 'Integritas Tinggi'],
            ['Manajemen Anggaran'],
            auth()->id()
        );

        return back()->with('success', 'Kandidat Suksesi Kepemimpinan Sekolah berhasil didaftarkan!');
    }

    public function pipelineReport()
    {
        $candidates = SuccessionCandidate::with(['position', 'candidate'])->get();

        return view('succession.pipeline-report', compact('candidates'));
    }
}
