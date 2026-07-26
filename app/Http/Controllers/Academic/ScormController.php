<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\ScormPackage;
use App\Models\ScormTracking;
use App\Services\Academic\ScormService;
use Illuminate\Http\Request;

class ScormController extends Controller
{
    public function packages()
    {
        $packages = ScormPackage::with('uploader')->latest()->get();
        return view('scorm.packages', compact('packages'));
    }

    public function storePackage(Request $request, ScormService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'version' => 'required|in:scorm12,scorm2004,xapi',
            'file_path' => 'required|string',
        ]);

        $pkg = $service->uploadPackage($validated, auth()->id());

        return redirect()->route('academic.scorm.packages')
            ->with('success', "Paket Konten SCORM [{$pkg->title}] berhasil diunggah!");
    }

    public function launch(int $id, ScormService $service)
    {
        $package = ScormPackage::findOrFail($id);
        $studentId = auth()->id();

        $tracking = ScormTracking::firstOrCreate(
            ['scorm_package_id' => $id, 'student_id' => $studentId],
            ['lesson_status' => 'incomplete', 'score_raw' => 0]
        );

        return view('scorm.player', compact('package', 'tracking'));
    }

    public function recordTracking(int $id, Request $request, ScormService $service)
    {
        $package = ScormPackage::findOrFail($id);

        $validated = $request->validate([
            'lesson_status' => 'required|in:not_attempted,incomplete,completed,passed,failed',
            'score_raw' => 'nullable|numeric',
        ]);

        $service->recordTracking($package, auth()->id(), $validated);

        return response()->json(['success' => true, 'message' => 'LRS Tracking synced!']);
    }
}
