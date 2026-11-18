<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Concerns\RespondsForClassSessions;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClassSessionResource;
use App\Http\Resources\SessionAttendanceResource;
use App\Models\ClassSession;
use App\Models\SessionAttendance;
use App\Services\Academic\ClassSessionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Sesi Kelas — Admin/Waka Kurikulum/TU
 *
 * Monitoring sesi kelas, koreksi presensi beralasan, dan rekap kehadiran per-mapel.
 *
 * @authenticated
 */
class AdminClassSessionController extends Controller
{
    use ApiResponseTrait, RespondsForClassSessions;

    public function __construct(private ClassSessionService $sessions) {}

    /**
     * Daftar Sesi Kelas
     *
     * Paginasi: `data = { items, current_page, last_page, per_page, total }`.
     *
     * @queryParam date string Tanggal (Y-m-d). Example: 2026-10-07
     * @queryParam teacher_id integer ID guru. Example: 8
     * @queryParam classroom_id integer ID kelas. Example: 3
     * @queryParam subject_id integer ID mapel. Example: 5
     * @queryParam status string scheduled, active, completed, cancelled, auto_closed. Example: auto_closed
     * @queryParam page integer Halaman. Example: 1
     * @queryParam per_page integer Jumlah per halaman (maks 100). Example: 20
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $filters = $this->validateEnvelope($request, [
                'date' => 'nullable|date_format:Y-m-d',
                'teacher_id' => 'nullable|integer',
                'classroom_id' => 'nullable|integer',
                'subject_id' => 'nullable|integer',
                'status' => ['nullable', Rule::in([
                    ClassSession::STATUS_SCHEDULED, ClassSession::STATUS_ACTIVE, ClassSession::STATUS_COMPLETED,
                    ClassSession::STATUS_CANCELLED, ClassSession::STATUS_AUTO_CLOSED,
                ])],
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);
            $page = $this->sessions->paginateSessions($filters, (int) ($filters['per_page'] ?? 20));

            return $this->successResponse([
                'items' => ClassSessionResource::collection($page->getCollection()),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ], __('Daftar sesi kelas berhasil diambil'));
        });
    }

    /**
     * Detail Presensi Sesi
     *
     * @urlParam sessionId integer required ID sesi kelas. Example: 45
     */
    public function sessionAttendances(int $sessionId): JsonResponse
    {
        return $this->handle(fn () => $this->successResponse(
            SessionAttendanceResource::collection($this->sessions->rosterOf($this->sessions->findSession($sessionId))),
            __('Detail presensi sesi berhasil diambil')
        ));
    }

    /**
     * Koreksi Presensi
     *
     * Hanya: alpha → hadir/sakit/izin, sakit/izin → hadir, telat → hadir. Kehadiran yang sudah tercatat
     * tidak bisa diubah menjadi alpha. Alasan wajib (min. 10 karakter) dan dicatat di audit log.
     *
     * @urlParam attendanceId integer required ID presensi. Example: 9001
     *
     * @bodyParam status string required hadir, sakit, atau izin. Example: izin
     * @bodyParam reason string required Alasan koreksi (min. 10 karakter). Example: Surat izin orang tua diterima TU
     */
    public function overrideAttendance(Request $request, int $attendanceId): JsonResponse
    {
        return $this->handle(function () use ($request, $attendanceId) {
            $data = $this->validateEnvelope($request, [
                'status' => ['required', Rule::in([SessionAttendance::STATUS_HADIR, SessionAttendance::STATUS_SAKIT, SessionAttendance::STATUS_IZIN])],
                'reason' => 'required|string|min:10|max:255',
            ], [
                'reason.required' => 'Alasan koreksi wajib diisi.',
                'reason.min' => 'Alasan koreksi minimal 10 karakter.',
            ]);
            $attendance = $this->sessions->override($request->user(), $attendanceId, $data['status'], $data['reason'], $request->ip());

            return $this->successResponse(new SessionAttendanceResource($attendance), __('Presensi berhasil dikoreksi'));
        });
    }

    /**
     * Rekap Presensi Per-Mapel
     *
     * `presence_rate` = (hadir + telat) / total, 0.0–1.0. Rentang maksimal 366 hari.
     *
     * @queryParam start_date string required Tanggal awal (Y-m-d). Example: 2026-10-01
     * @queryParam end_date string required Tanggal akhir (Y-m-d). Example: 2026-10-31
     * @queryParam classroom_id integer ID kelas. Example: 3
     * @queryParam subject_id integer ID mapel. Example: 5
     * @queryParam group_by string student, class, atau subject (default student). Example: student
     */
    public function attendanceReport(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $data = $this->validateEnvelope($request, [
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
                'classroom_id' => 'nullable|integer',
                'subject_id' => 'nullable|integer',
                'group_by' => ['nullable', Rule::in(['student', 'class', 'subject'])],
            ]);

            if (now()->parse($data['start_date'])->diffInDays(now()->parse($data['end_date'])) > 366) {
                return $this->errorResponse(__('Rentang laporan maksimal 366 hari.'), 422);
            }

            $report = $this->sessions->report(
                $data['start_date'],
                $data['end_date'],
                isset($data['classroom_id']) ? (int) $data['classroom_id'] : null,
                isset($data['subject_id']) ? (int) $data['subject_id'] : null,
                $data['group_by'] ?? 'student'
            );

            return $this->successResponse($report, __('Rekap presensi berhasil diambil'));
        });
    }
}
