<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Achievement & Portfolio Mobile API v1
 *
 * REST API Endpoints untuk integrasi modul Portofolio Prestasi, E-Sertifikat Digital, dan CV Akademik SNBP pada aplikasi Android Sulaone.
 */
class AchievementMobileApiController extends Controller
{
    protected AchievementService $service;

    public function __construct(AchievementService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/achievements/list
     * Mengambil daftar prestasi siswa yang terautentikasi.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user?->student;

        if (! $student && $request->filled('student_id') && in_array($user?->role, ['guru', 'admin', 'superadmin'], true)) {
            $student = Student::find($request->student_id);
        }

        if (! $student) {
            return response()->json([]);
        }

        $data = $this->service->getAchievements($student);

        return response()->json($data);
    }

    /**
     * POST /api/v1/achievements/upload
     * Mengunggah bukti prestasi / sertifikat lomba baru (mendukung Base64 dan Multipart file maks 5MB).
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'field' => 'required|string|max:100',
            'level' => 'required|string|max:50',
            'organizer' => 'required|string|max:255',
            'date' => 'required',
            'image_base64' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'certificate_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = $request->user();
        $student = $user?->student;

        if (! $student) {
            return response()->json([
                'success' => false,
                'error' => 'Akses ditolak.',
                'message' => 'Hanya akun siswa yang dapat mengunggah bukti prestasi.',
            ], 422);
        }

        $uploadedFile = $request->file('file') ?: $request->file('certificate_file');

        try {
            $achievement = $this->service->uploadAchievement($student, $request->all(), $uploadedFile);

            return response()->json($achievement->toMobileItemArray(), 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengunggah prestasi.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v1/achievements/certificates
     * Mengambil daftar e-sertifikat digital siswa yang terverifikasi.
     */
    public function certificates(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user?->student;

        if (! $student) {
            return response()->json([]);
        }

        $data = $this->service->getCertificates($student);

        return response()->json($data);
    }

    /**
     * GET /api/v1/achievements/academic-cv-summary
     * Mengambil ringkasan CV Akademik untuk SNBP.
     */
    public function cvSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user?->student;

        if (! $student) {
            return response()->json([
                'error' => 'Data siswa tidak ditemukan.',
                'message' => 'Pengguna tidak memiliki profil siswa aktif.',
            ], 422);
        }

        $summary = $this->service->getAcademicCvSummary($student);

        return response()->json($summary);
    }

    /**
     * POST /api/v1/achievements/{id}/verify
     * Verifikasi prestasi siswa oleh Waka Kesiswaan / Guru / Admin (Approval Workflow).
     */
    public function verify(Request $request, $id): JsonResponse
    {
        $request->validate([
            'approve' => 'required|boolean',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $authorizedRoles = ['guru', 'teacher', 'admin', 'superadmin', 'waka_kesiswaan', 'kepala_sekolah', 'kepsek'];
        if (! $user || ! in_array($user->role, $authorizedRoles, true)) {
            return response()->json([
                'success' => false,
                'error' => 'Otorisasi ditolak.',
                'message' => 'Hanya Guru, Waka Kesiswaan, dan Administrator yang memiliki izin memverifikasi prestasi.',
            ], 403);
        }

        try {
            $achievement = $this->service->verifyAchievement(
                (int) $id,
                $user,
                (bool) $request->approve,
                $request->input('rejection_reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Status verifikasi prestasi berhasil diperbarui.',
                'data' => $achievement->toMobileItemArray(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui status.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
