<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use App\Services\Student\StudentSelfService;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    public function dashboard()
    {
        $requests = StudentRequest::where('student_id', auth()->id())->latest()->get();

        return view('student.portal.dashboard', compact('requests'));
    }

    public function createRequest()
    {
        return view('student.portal.request-form');
    }

    public function storeRequest(Request $request, StudentSelfService $service)
    {
        $validated = $request->validate([
            'request_type' => 'required|in:transcript,certificate,recommendation_letter,profile_update,document_copy',
            'notes' => 'nullable|string',
        ]);

        $service->submitRequest(auth()->id(), $validated['request_type'], ['notes' => $validated['notes'] ?? '']);

        return redirect()->route('student.portal.dashboard')->with('success', 'Pengajuan dokumen mandiri berhasil dikirim!');
    }
}
