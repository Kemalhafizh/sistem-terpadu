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
            // Asumsi 1 parent = 1 student aktif untuk versi mobile (bisa dimodif via query param)
            $query = \App\Models\Student::where('parent_id', $user->id);
            $studentId = $request->query('student_id');
            if ($studentId) {
                return $query->where('id', $studentId)->first();
            }
            return $query->first();
        }
        
        throw new Exception("Role tidak memiliki data siswa.");
    }

    public function summary(Request $request)
    {
        try {
            $student = $this->resolveStudent($request);
            if (!$student) return $this->errorResponse('Siswa tidak ditemukan.', 404);

            $data = $this->disciplineService->getSummary($student);
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

            $data = $this->disciplineService->getWarningLetters($student);
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
