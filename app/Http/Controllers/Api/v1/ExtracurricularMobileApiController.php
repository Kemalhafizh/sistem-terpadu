<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ExtracurricularService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Extracurricular & OSIS Mobile API v1
 *
 * REST API Endpoints untuk integrasi modul Ekstrakurikuler dan Papan Pengumuman OSIS pada aplikasi Android Sulaone.
 */
class ExtracurricularMobileApiController extends Controller
{
    protected ExtracurricularService $service;

    public function __construct(ExtracurricularService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/extracurricular/list
     * Daftar ekstrakurikuler aktif beserta informasi jadwal, pembina, kuota, dan status keikutsertaan siswa.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        $data = $this->service->getEkskulList($student);

        return response()->json($data);
    }

    /**
     * POST /api/v1/extracurricular/{id}/register
     * Pendaftaran mandiri siswa ke kegiatan ekstrakurikuler.
     */
    public function register(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $student = $user?->student;

        if (! $student) {
            return response()->json([
                'success' => false,
                'error' => 'Akses ditolak.',
                'message' => 'Hanya akun siswa yang dapat mendaftar ekstrakurikuler.',
            ], 422);
        }

        try {
            $member = $this->service->register($student, (int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendaftar di kegiatan ekstrakurikuler.',
                'member_id' => $member->id,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Pendaftaran gagal.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/extracurricular/attendance/submit
     * Pencatatan presensi latihan ekstrakurikuler (hanya oleh Pembina/Pelatih atau Admin).
     */
    public function submitAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'ekskul_id' => 'required|exists:extracurriculars,id',
            'student_id' => 'required',
            'qr_payload' => 'nullable|string',
        ]);

        try {
            $attendance = $this->service->submitAttendance(
                $request->user(),
                (int) $request->ekskul_id,
                $request->student_id,
                $request->qr_payload
            );

            return response()->json([
                'success' => true,
                'message' => 'Presensi latihan berhasil dicatat.',
                'attendance_id' => $attendance->id,
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Otorisasi ditolak.',
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mencatat presensi.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v1/extracurricular/osis-feed
     * Feed berita, kegiatan, dan pengumuman resmi OSIS.
     */
    public function osisFeed(Request $request): JsonResponse
    {
        $isPaginated = $request->boolean('paginated') || $request->has('page');
        $limit = $request->integer('per_page', $request->integer('limit', 20));

        $data = $this->service->getOsisFeed($limit, $isPaginated);

        return response()->json($data);
    }
}
