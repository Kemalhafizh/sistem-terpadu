<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ElearningMobileService;
use App\Models\ElearningMaterial;
use App\Models\ElearningAssignment;
use App\Models\ElearningSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ElearningMobileApiController extends Controller
{
    protected ElearningMobileService $service;

    public function __construct(ElearningMobileService $service)
    {
        $this->service = $service;
    }

    public function studentClasses(Request $request): JsonResponse
    {
        $classes = $this->service->getStudentClasses($request->user()->id);
        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    public function studentMaterials(Request $request, int $classId): JsonResponse
    {
        $materials = $this->service->getClassMaterials($classId);
        return response()->json([
            'success' => true,
            'data' => $materials,
        ]);
    }

    public function studentAssignments(Request $request, int $classId): JsonResponse
    {
        $assignments = $this->service->getClassAssignments($classId, $request->user()->id);
        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    public function submitAssignment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,zip,jpg,png',
        ]);

        $submission = $this->service->submitAssignment(
            $id,
            $request->user()->id,
            $request->input('content'),
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dikumpulkan',
            'data' => $submission,
        ]);
    }

    public function teacherClasses(Request $request): JsonResponse
    {
        $classes = $this->service->getTeacherClasses($request->user()->id);
        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    public function storeMaterial(Request $request, int $classId): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'nullable|in:pdf,video,link,document',
            'file' => 'nullable|file|max:20480',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('uploads/elearning/materials', 'public');
        }

        $material = ElearningMaterial::create([
            'elearning_class_id' => $classId,
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type ?? 'document',
            'file_path' => $filePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil diunggah',
            'data' => $material,
        ], 201);
    }

    public function storeAssignment(Request $request, int $classId): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'required|date',
        ]);

        $assignment = ElearningAssignment::create([
            'elearning_class_id' => $classId,
            'title' => $request->title,
            'description' => $request->description,
            'due_at' => $request->due_at,
            'allow_late' => (bool) $request->input('allow_late', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas baru berhasil diterbitkan',
            'data' => $assignment,
        ], 201);
    }

    public function getSubmissions(Request $request, int $id): JsonResponse
    {
        $submissions = ElearningSubmission::with('student')
            ->where('elearning_assignment_id', $id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $submissions,
        ]);
    }

    public function gradeSubmission(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $sub = $this->service->gradeSubmission(
            $id,
            (int) $request->score,
            $request->feedback
        );

        return response()->json([
            'success' => true,
            'message' => 'Penilaian tugas berhasil disimpan',
            'data' => $sub,
        ]);
    }
}
