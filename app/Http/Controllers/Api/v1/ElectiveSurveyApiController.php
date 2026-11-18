<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ClassPlacement;
use App\Models\ElectiveSurvey;
use App\Models\Student;
use App\Services\Academic\ElectiveSurveyService;
use App\Support\Access\StudentDataAccess;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ElectiveSurveyApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ElectiveSurveyService $surveyService,
    ) {}

    public function studentSurvey(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        if (! $student) {
            return $this->errorResponse(__('Profil siswa tidak ditemukan.'), 404);
        }

        return $this->successResponse(
            $this->buildSurveyPayload($student),
            __('Data survei peminatan berhasil dimuat.')
        );
    }

    public function submitChoices(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        if (! $student) {
            return $this->errorResponse(__('Profil siswa tidak ditemukan.'), 404);
        }

        $validated = $request->validate([
            'survey_id' => ['nullable', 'integer', 'exists:elective_surveys,id'],
            'subject_ids' => ['required', 'array'],
            'subject_ids.*' => ['integer'],
            'ranks' => ['nullable', 'array'],
        ]);

        $survey = isset($validated['survey_id'])
            ? ElectiveSurvey::findOrFail($validated['survey_id'])
            : ElectiveSurvey::query()->latest('id')->first();

        if (! $survey) {
            return $this->errorResponse(__('Belum ada survei peminatan yang aktif.'), 404);
        }

        try {
            $this->surveyService->submitChoices(
                $survey,
                $student,
                $validated['subject_ids'],
                $validated['ranks'] ?? null
            );
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorCode = $errors['error_code'][0] ?? 'VALIDATION_ERROR';
            unset($errors['error_code']);
            $firstMessage = collect($errors)->flatten()->first() ?? __('Pilihan mata pelajaran tidak valid.');

            return response()->json([
                'status' => 'error',
                'success' => false,
                'error_code' => $errorCode,
                'message' => $firstMessage,
                'errors' => $errors,
            ], 422);
        }

        return $this->successResponse(
            $this->buildSurveyPayload($student, $survey),
            __('Pilihan mata pelajaran peminatan berhasil disimpan.')
        );
    }

    public function studentNextClassroom(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        if (! $student) {
            return $this->errorResponse(__('Profil siswa tidak ditemukan.'), 404);
        }

        return $this->successResponse(
            $this->buildNextClassroomPayload($student),
            __('Data kelas tahun ajaran berikutnya berhasil dimuat.')
        );
    }

    public function studentEnrollmentHistory(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        if (! $student) {
            return $this->errorResponse(__('Profil siswa tidak ditemukan.'), 404);
        }

        return $this->successResponse(
            $this->buildEnrollmentHistoryPayload($student),
            __('Riwayat rombel berhasil dimuat.')
        );
    }

    public function parentChildSurvey(Request $request, string $student): JsonResponse
    {
        $studentModel = $this->resolveStudent($student);
        StudentDataAccess::authorizeStudentRecord($request->user(), $studentModel);

        return $this->successResponse([
            'survey' => $this->buildSurveyPayload($studentModel),
            'next_classroom' => $this->buildNextClassroomPayload($studentModel),
        ], __('Data peminatan anak berhasil dimuat.'));
    }

    public function parentChildEnrollmentHistory(Request $request, string $student): JsonResponse
    {
        $studentModel = $this->resolveStudent($student);
        StudentDataAccess::authorizeStudentRecord($request->user(), $studentModel);

        return $this->successResponse(
            $this->buildEnrollmentHistoryPayload($studentModel),
            __('Riwayat rombel anak berhasil dimuat.')
        );
    }

    private function resolveStudent(string $identifier): ?Student
    {
        $byUuid = Student::where('uuid', $identifier)->first();
        if ($byUuid) {
            return $byUuid;
        }

        if (ctype_digit($identifier)) {
            return Student::find((int) $identifier);
        }

        return null;
    }

    private function buildSurveyPayload(Student $student, ?ElectiveSurvey $survey = null): ?array
    {
        $survey ??= ElectiveSurvey::query()
            ->whereIn('status', [ElectiveSurvey::STATUS_OPEN, ElectiveSurvey::STATUS_CLOSED, ElectiveSurvey::STATUS_COMPLETED])
            ->latest('id')
            ->first();

        if (! $survey) {
            return null;
        }

        $offered = $survey->subjects()->orderBy('name')->get()->map(fn ($s) => [
            'id' => (int) $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'category' => $s->category,
        ])->values()->all();

        $myChoices = $student->electiveChoices()
            ->where('elective_survey_id', $survey->id)
            ->with('subject')
            ->orderBy('rank')
            ->orderBy('id')
            ->get()
            ->map(fn ($c) => [
                'subject_id' => (int) $c->subject_id,
                'name' => $c->subject?->name,
                'code' => $c->subject?->code,
                'rank' => $c->rank,
            ])
            ->values()
            ->all();

        return [
            'id' => (int) $survey->id,
            'uuid' => $survey->uuid,
            'title' => $survey->title,
            'school_year' => $survey->schoolYear?->name,
            'status' => $survey->status,
            'status_code' => $survey->status,
            'is_open' => $survey->isOpen(),
            'min_choices' => (int) $survey->min_choices,
            'max_choices' => (int) $survey->max_choices,
            'ranked' => (bool) $survey->ranked,
            'opens_at' => $survey->opens_at?->toIso8601String(),
            'closes_at' => $survey->closes_at?->toIso8601String(),
            'announced_at' => $survey->announced_at?->toIso8601String(),
            'offered_subjects' => $offered,
            'my_choices' => $myChoices,
        ];
    }

    private function buildNextClassroomPayload(Student $student): ?array
    {
        $completedSurvey = ElectiveSurvey::query()
            ->where('status', ElectiveSurvey::STATUS_COMPLETED)
            ->latest('id')
            ->first();

        if (! $completedSurvey) {
            return null;
        }

        $placement = ClassPlacement::query()
            ->where('school_year_id', $completedSurvey->school_year_id)
            ->where('student_id', $student->id)
            ->with(['classroom.electiveSubjects', 'classroom.teacher', 'schoolYear'])
            ->first();

        if (! $placement || ! $placement->classroom) {
            return null;
        }

        return [
            'school_year' => $placement->schoolYear?->name,
            'classroom_id' => (int) $placement->classroom->id,
            'classroom_name' => $placement->classroom->name,
            'grade_level' => $placement->classroom->grade_level,
            'homeroom_teacher' => $placement->classroom->teacher?->name,
            'elective_subjects' => $placement->classroom->electiveSubjects->map(fn ($s) => [
                'id' => (int) $s->id,
                'name' => $s->name,
                'code' => $s->code,
            ])->values()->all(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildEnrollmentHistoryPayload(Student $student): array
    {
        return $student->enrollments()
            ->with(['classroom.teacher', 'schoolYear'])
            ->orderByDesc('started_on')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($e) => [
                'id' => (int) $e->id,
                'school_year' => $e->schoolYear?->name ?? $e->classroom?->academic_year,
                'classroom_id' => (int) $e->classroom_id,
                'classroom_name' => $e->classroom?->name,
                'grade_level' => $e->classroom?->grade_level,
                'homeroom_teacher' => $e->classroom?->teacher?->name,
                'status' => $e->status,
                'status_code' => $e->status,
                'started_on' => $e->started_on?->format('Y-m-d'),
                'ended_on' => $e->ended_on?->format('Y-m-d'),
                'ended_reason' => $e->ended_reason,
            ])
            ->values()
            ->all();
    }
}
