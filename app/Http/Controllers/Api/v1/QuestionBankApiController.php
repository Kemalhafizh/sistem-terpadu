<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\QuestionBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionBankApiController extends Controller
{
    protected QuestionBankService $service;

    public function __construct(QuestionBankService $service)
    {
        $this->service = $service;
    }

    public function getCategories(Request $request): JsonResponse
    {
        $subjectId = $request->query('subject_id') ? (int) $request->query('subject_id') : null;
        $categories = $this->service->getCategories($subjectId);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function getItems(Request $request): JsonResponse
    {
        $filters = $request->only([
            'category_id',
            'difficulty',
            'cognitive_level',
            'type',
            'is_validated',
            'search',
            'per_page',
        ]);

        $items = $this->service->getItems($filters);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:question_bank_categories,id',
            'question_text' => 'required|string',
            'correct_answer' => 'required|string',
            'difficulty' => 'nullable|in:mudah,sedang,sulit',
            'cognitive_level' => 'nullable|in:C1,C2,C3,C4,C5,C6',
            'type' => 'nullable|in:pilihan_ganda,essay,benar_salah,menjodohkan,isian_singkat',
            'options' => 'nullable|array',
        ]);

        try {
            $data = $request->all();
            $data['created_by'] = $request->user()->id;
            $item = $this->service->createQuestion($data);

            return response()->json([
                'success' => true,
                'message' => __('Soal berhasil ditambahkan ke bank soal'),
                'data' => $item,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function autoGenerate(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'nullable|exists:question_bank_categories,id',
            'total_questions' => 'required|integer|min:1|max:100',
            'exam_id' => 'nullable|exists:exams,id',
            'difficulty_distribution' => 'nullable|array',
        ]);

        // With an exam_id the service replaces that exam's questions. Any teacher
        // could do that to any exam, even one already being taken.
        if ($request->filled('exam_id')) {
            $exam = Exam::findOrFail($request->integer('exam_id'));
            $user = $request->user();
            if ($user->role !== 'superadmin' && $exam->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('Hanya pembuat ujian yang dapat mengganti soal ujian ini.'),
                ], 403);
            }
            if (ExamAttempt::where('exam_id', $exam->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Soal ujian tidak bisa diganti karena sudah ada siswa yang mengerjakan.'),
                ], 422);
            }
        }

        $result = $this->service->autoGenerateExam($request->all());

        return response()->json([
            'success' => true,
            'message' => $request->filled('exam_id')
                ? __('Soal ujian diganti dengan paket dari bank soal.')
                : __('Paket soal dipilih dari bank soal.'),
            'data' => $result,
        ]);
    }
}
