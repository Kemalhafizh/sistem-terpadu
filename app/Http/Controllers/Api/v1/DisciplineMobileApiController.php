<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\DisciplineService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class DisciplineMobileApiController extends Controller
{
    use ApiResponseTrait;

    protected DisciplineService $disciplineService;

    public function __construct(DisciplineService $disciplineService)
    {
        $this->disciplineService = $disciplineService;
    }

    private function resolveStudent(Request $request)
    {
        $user = $request->user();
        if (in_array($user->role, ['student', 'siswa'])) {
            return $user->student;
        } elseif (in_array($user->role, ['parent', 'orangtua'])) {
            // A parent may have several children. The app identifies a child
            // by uuid (all it learns from parent/children); student_id is kept
            // for older clients. Either way the child must belong to this
            // parent — someone else's child resolves to null (404).
            $query = \App\Models\Student::where('parent_id', $user->id);
            if ($request->filled('student_uuid')) {
                return $query->where('uuid', (string) $request->query('student_uuid'))->first();
            }
            if ($request->filled('student_id')) {
                return $query->where('id', $request->query('student_id'))->first();
            }
            // No child chosen: a stable default (oldest record) instead of
            // whatever row the database happened to return first. The
            // summary names the child, so the app can show whose data it is.
            return $query->orderBy('id')->first();
        }
        
        throw new Exception("Role tidak memiliki data siswa.");
    }

    public function summary(Request $request)
    {
        try {
            $student = $this->resolveStudent($request);
            if (!$student) return $this->errorResponse('Siswa tidak ditemukan.', 404);

            $data = $this->disciplineService->getSummary($student);
            // Whose numbers these are — essential for a parent with several children.
            $data['student'] = [
                'uuid' => $student->uuid,
                'name' => $student->name,
                'classroom' => $student->classroom?->name,
            ];
            return $this->successResponse($data, 'Ringkasan poin kedisiplinan berhasil diambil');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function history(Request $request)
    {
        try {
            $student = $this->resolveStudent($request);
            if (!$student) return $this->errorResponse('Siswa tidak ditemukan.', 404);

            // Unpaginated — every other mobile list endpoint in this app returns
            // a flat array, and Android's DisciplineApiService expects one too
            // (a Laravel paginator's {current_page, data: [...], ...} shape
            // would otherwise double-nest under the ApiEnvelope's own "data").
            $data = $this->disciplineService->getHistory($student, false);
            return $this->successResponse($data, 'Riwayat poin kedisiplinan berhasil diambil');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function letters(Request $request)
    {
        try {
            $student = $this->resolveStudent($request);
            if (!$student) return $this->errorResponse('Siswa tidak ditemukan.', 404);

            $data = $this->disciplineService->getWarningLetters($student, $request->user());
            return $this->successResponse($data, 'Daftar surat peringatan berhasil diambil');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function signLetter(Request $request, $id)
    {
        $request->validate([
            'digital_signature' => 'required|string',
        ]);

        try {
            $user = $request->user();
            $letter = $this->disciplineService->signWarningLetter($id, $user, $request->digital_signature);

            return $this->successResponse([
                'signed_at' => $letter->signed_at->format('Y-m-d H:i:s'),
            ], 'Surat Peringatan berhasil ditandatangani.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}
