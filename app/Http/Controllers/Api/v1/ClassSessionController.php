<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Concerns\RespondsForClassSessions;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClassSessionResource;
use App\Http\Resources\SessionAttendanceResource;
use App\Models\SessionAttendance;
use App\Services\Academic\ClassSessionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Sesi Kelas — Guru (FASE 117)
 *
 * Memulai/mengakhiri sesi kelas hidup, menampilkan QR berputar, dan absensi manual per sesi.
 *
 * @authenticated
 */
class ClassSessionController extends Controller
{
    use ApiResponseTrait, RespondsForClassSessions;

    public function __construct(private ClassSessionService $sessions) {}

    /**
     * Sesi Kelas Hari Ini
     *
     * Daftar sesi kelas guru hari ini, satu per entri jadwal (status scheduled/active/completed/auto_closed).
     * `can_start` bernilai true saat tombol "Mulai Kelas" boleh diaktifkan.
     */
    public function todaySessions(Request $request): JsonResponse
    {
        $sessions = $this->sessions->todaySessionsForTeacher($request->user());

        return $this->successResponse(ClassSessionResource::collection($sessions), __('Sesi kelas hari ini berhasil diambil'));
    }

    /**
     * Mulai Kelas
     *
     * Memulai sesi untuk jadwal ini: hanya guru yang dijadwalkan, pada hari jadwal, dari 10 menit
     * sebelum jam mulai hingga jam selesai. Seluruh siswa kelas otomatis tercatat alpha sampai scan QR.
     *
     * @urlParam scheduleId integer required ID jadwal. Example: 12
     *
     * @bodyParam topic string Topik/materi yang akan diajarkan. Example: Limit fungsi aljabar
     */
    public function startSession(Request $request, int $scheduleId): JsonResponse
    {
        return $this->handle(function () use ($request, $scheduleId) {
            $data = $this->validateEnvelope($request, ['topic' => 'nullable|string|max:200']);
            $session = $this->sessions->start($request->user(), $scheduleId, $data['topic'] ?? null);

            return $this->successResponse(new ClassSessionResource($session), __('Sesi kelas dimulai'), 201);
        });
    }

    /**
     * Akhiri Kelas
     *
     * Menutup sesi aktif. Siswa yang belum tercatat hadir tetap alpha (final). Bila ada topik,
     * draf Jurnal Mengajar dibuat otomatis.
     *
     * @urlParam sessionId integer required ID sesi kelas. Example: 45
     *
     * @bodyParam notes string Catatan KBM hari ini. Example: Siswa aktif berdiskusi.
     * @bodyParam topic string Topik/materi yang diajarkan. Example: Limit fungsi aljabar
     */
    public function endSession(Request $request, int $sessionId): JsonResponse
    {
        return $this->handle(function () use ($request, $sessionId) {
            $data = $this->validateEnvelope($request, [
                'notes' => 'nullable|string|max:500',
                'topic' => 'nullable|string|max:200',
            ]);
            $session = $this->sessions->end($request->user(), $sessionId, $data['notes'] ?? null, $data['topic'] ?? null);

            return $this->successResponse(new ClassSessionResource($session), __('Sesi kelas diakhiri'));
        });
    }

    /**
     * QR Sesi Aktif
     *
     * Token QR saat ini (berganti setiap 30 detik). Encode `qr_token` apa adanya ke QR; panggil ulang
     * sebelum `remaining_seconds` habis.
     *
     * @urlParam sessionId integer required ID sesi kelas. Example: 45
     */
    public function getActiveQr(Request $request, int $sessionId): JsonResponse
    {
        return $this->handle(fn () => $this->successResponse(
            $this->sessions->currentQr($request->user(), $sessionId),
            'QR sesi kelas berhasil diambil'
        ));
    }

    /**
     * Daftar Hadir Sesi
     *
     * Status presensi real-time seluruh siswa di sesi ini (diurutkan nama).
     *
     * @urlParam sessionId integer required ID sesi kelas. Example: 45
     */
    public function sessionStudentList(Request $request, int $sessionId): JsonResponse
    {
        return $this->handle(fn () => $this->successResponse(
            SessionAttendanceResource::collection($this->sessions->roster($request->user(), $sessionId)),
            __('Daftar hadir sesi berhasil diambil')
        ));
    }

    /**
     * Absensi Manual
     *
     * Guru menandai satu siswa (hanya saat sesi aktif).
     *
     * @urlParam sessionId integer required ID sesi kelas. Example: 45
     *
     * @bodyParam student_id integer required ID siswa. Example: 301
     * @bodyParam status string required hadir, telat, sakit, izin, atau alpha. Example: sakit
     * @bodyParam notes string Catatan. Example: Surat dokter menyusul
     */
    public function manualAttendance(Request $request, int $sessionId): JsonResponse
    {
        return $this->handle(function () use ($request, $sessionId) {
            $data = $this->validateEnvelope($request, [
                'student_id' => 'required|integer',
                'status' => ['required', 'string', Rule::in(SessionAttendance::STATUSES)],
                'notes' => 'nullable|string|max:255',
            ]);
            $attendance = $this->sessions->markManual(
                $request->user(), $sessionId, (int) $data['student_id'], $data['status'], $data['notes'] ?? null
            );

            return $this->successResponse(new SessionAttendanceResource($attendance), __('Presensi siswa diperbarui'));
        });
    }

    /**
     * Absensi Manual Massal
     *
     * Menyimpan banyak perubahan sekaligus. Item yang gagal dilaporkan di `errors` tanpa membatalkan item lain.
     *
     * @urlParam sessionId integer required ID sesi kelas. Example: 45
     *
     * @bodyParam attendances object[] required Daftar perubahan.
     * @bodyParam attendances[].student_id integer required ID siswa. Example: 301
     * @bodyParam attendances[].status string required hadir, telat, sakit, izin, atau alpha. Example: izin
     * @bodyParam attendances[].notes string Catatan. Example: Dispensasi lomba
     */
    public function bulkManualAttendance(Request $request, int $sessionId): JsonResponse
    {
        return $this->handle(function () use ($request, $sessionId) {
            $data = $this->validateEnvelope($request, [
                'attendances' => 'required|array|min:1|max:100',
                'attendances.*.student_id' => 'required|integer|distinct',
                'attendances.*.status' => ['required', 'string', Rule::in(SessionAttendance::STATUSES)],
                'attendances.*.notes' => 'nullable|string|max:255',
            ]);
            $result = $this->sessions->markManualBulk($request->user(), $sessionId, $data['attendances']);

            return $this->successResponse($result, "{$result['updated']} presensi diperbarui, {$result['failed']} gagal");
        });
    }
}
