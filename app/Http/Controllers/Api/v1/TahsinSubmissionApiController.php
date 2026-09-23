<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\TahsinAnnotation;
use App\Models\TahsinSubmission;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * FASE 72.1: Audio Recorder & Live Waveform Canvas untuk Tahsin/Tahfidz.
 * Students submit a recitation recording; it's routed to their homeroom
 * teacher, who listens and pins timestamped tajwid feedback on the waveform.
 */
class TahsinSubmissionApiController extends Controller
{
    use ApiResponseTrait;

    private const TEACHER_ROLES = ['guru', 'teacher', 'bk', 'admin', 'superadmin'];

    /**
     * POST /api/v1/tahsin/submissions — student uploads a recitation recording.
     */
    public function store(Request $request)
    {
        $request->validate([
            'surah_name' => 'required|string|max:255',
            'start_ayah' => 'nullable|integer|min:1',
            'end_ayah' => 'nullable|integer|min:1',
            'duration_seconds' => 'required|integer|min:1|max:5400',
            'audio' => 'required|file|mimes:m4a,mp4,aac,mp3,wav|max:20480',
        ]);

        $student = $request->user()->student;
        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $audioPath = $request->file('audio')->store('tahsin_submissions', 'public');

        $submission = TahsinSubmission::create([
            'student_id' => $student->id,
            'teacher_id' => $student->classroom?->teacher_id,
            'surah_name' => $request->input('surah_name'),
            'start_ayah' => $request->input('start_ayah'),
            'end_ayah' => $request->input('end_ayah'),
            'audio_path' => $audioPath,
            'duration_seconds' => (int) $request->input('duration_seconds'),
            'status' => 'pending',
        ]);

        return $this->successResponse(
            $this->toMobileArray($submission->fresh()),
            'Setoran tahsin berhasil dikirim ke guru pembimbing',
            201
        );
    }

    /**
     * GET /api/v1/tahsin/submissions — student's own submission history.
     */
    public function myIndex(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $submissions = TahsinSubmission::where('student_id', $student->id)
            ->withCount('annotations')
            ->latest()
            ->get();

        return $this->successResponse(
            $submissions->map(fn ($s) => $this->toMobileArray($s))->values(),
            'Riwayat setoran tahsin berhasil diambil'
        );
    }

    /**
     * GET /api/v1/teacher/tahsin/submissions — submissions assigned to the
     * authenticated teacher, optionally filtered by ?status=pending|reviewed.
     */
    public function teacherIndex(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, self::TEACHER_ROLES, true)) {
            return $this->errorResponse('Otorisasi ditolak', 403);
        }

        $query = TahsinSubmission::with('student')->withCount('annotations');

        if (!in_array($user->role, ['admin', 'superadmin'], true)) {
            $query->where('teacher_id', $user->id);
        }

        if ($request->filled('status') && in_array($request->input('status'), ['pending', 'reviewed'], true)) {
            $query->where('status', $request->input('status'));
        }

        $submissions = $query->latest()->get();

        return $this->successResponse(
            $submissions->map(fn ($s) => $this->toMobileArray($s, includeStudent: true))->values(),
            'Daftar setoran tahsin berhasil diambil'
        );
    }

    /**
     * GET /api/v1/tahsin/submissions/{id} — full detail + annotations.
     * Shared by student (owner) and teacher (assignee/admin) — the mobile
     * app hits this same endpoint from either role's screen.
     */
    public function show(Request $request, $id)
    {
        $submission = TahsinSubmission::with(['student', 'annotations.teacher'])->find($id);
        if (!$submission) {
            return $this->errorResponse('Setoran tahsin tidak ditemukan', 404);
        }

        $user = $request->user();
        $isOwner = $user->student && (int) $user->student->id === (int) $submission->student_id;
        $isAssignedTeacher = (int) $submission->teacher_id === (int) $user->id;
        $isPrivileged = in_array($user->role, ['admin', 'superadmin'], true);

        if (!$isOwner && !$isAssignedTeacher && !$isPrivileged) {
            return $this->errorResponse('Anda tidak berwenang melihat setoran ini', 403);
        }

        return $this->successResponse(
            $this->toMobileArray($submission, includeStudent: true, includeAnnotations: true),
            'Detail setoran tahsin berhasil diambil'
        );
    }

    /**
     * POST /api/v1/teacher/tahsin/submissions/{id}/annotate — pin a
     * timestamped tajwid note on the waveform.
     */
    public function annotate(Request $request, $id)
    {
        $request->validate([
            'timestamp_seconds' => 'required|integer|min:0',
            'note' => 'required|string|max:1000',
            'tajwid_category' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        if (!in_array($user->role, self::TEACHER_ROLES, true)) {
            return $this->errorResponse('Otorisasi ditolak', 403);
        }

        $submission = TahsinSubmission::find($id);
        if (!$submission) {
            return $this->errorResponse('Setoran tahsin tidak ditemukan', 404);
        }

        $isAssignedTeacher = (int) $submission->teacher_id === (int) $user->id;
        $isPrivileged = in_array($user->role, ['admin', 'superadmin'], true);
        if (!$isAssignedTeacher && !$isPrivileged) {
            return $this->errorResponse('Hanya guru pembimbing yang dapat memberi catatan pada setoran ini', 403);
        }

        $annotation = $submission->annotations()->create([
            'teacher_id' => $user->id,
            'timestamp_seconds' => (int) $request->input('timestamp_seconds'),
            'note' => $request->input('note'),
            'tajwid_category' => $request->input('tajwid_category'),
        ]);

        return $this->successResponse(
            $this->annotationToArray($annotation->fresh('teacher')),
            'Catatan tajwid berhasil disimpan',
            201
        );
    }

    /**
     * POST /api/v1/teacher/tahsin/submissions/{id}/mark-reviewed — teacher
     * signals they're done annotating this recording.
     */
    public function markReviewed(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, self::TEACHER_ROLES, true)) {
            return $this->errorResponse('Otorisasi ditolak', 403);
        }

        $submission = TahsinSubmission::find($id);
        if (!$submission) {
            return $this->errorResponse('Setoran tahsin tidak ditemukan', 404);
        }

        $isAssignedTeacher = (int) $submission->teacher_id === (int) $user->id;
        $isPrivileged = in_array($user->role, ['admin', 'superadmin'], true);
        if (!$isAssignedTeacher && !$isPrivileged) {
            return $this->errorResponse('Hanya guru pembimbing yang dapat menyelesaikan evaluasi ini', 403);
        }

        $submission->update(['status' => 'reviewed', 'reviewed_at' => now()]);

        return $this->successResponse(
            $this->toMobileArray($submission->fresh(), includeStudent: true, includeAnnotations: true),
            'Evaluasi setoran tahsin ditandai selesai'
        );
    }

    private function toMobileArray(TahsinSubmission $s, bool $includeStudent = false, bool $includeAnnotations = false): array
    {
        $data = [
            'id' => $s->id,
            'uuid' => $s->uuid,
            'surah_name' => $s->surah_name,
            'start_ayah' => $s->start_ayah,
            'end_ayah' => $s->end_ayah,
            'audio_url' => Storage::url($s->audio_path),
            'duration_seconds' => $s->duration_seconds,
            'status' => $s->status,
            'annotation_count' => $s->annotations_count ?? $s->annotations()->count(),
            'reviewed_at' => $s->reviewed_at?->toIso8601String(),
            'created_at' => $s->created_at?->toIso8601String(),
        ];

        if ($includeStudent && $s->relationLoaded('student') && $s->student) {
            $data['student_id'] = $s->student->id;
            $data['student_name'] = $s->student->name;
        }

        if ($includeAnnotations) {
            $data['annotations'] = $s->annotations->map(fn ($a) => $this->annotationToArray($a))->values();
        }

        return $data;
    }

    private function annotationToArray(TahsinAnnotation $a): array
    {
        return [
            'id' => $a->id,
            'timestamp_seconds' => $a->timestamp_seconds,
            'note' => $a->note,
            'tajwid_category' => $a->tajwid_category,
            'teacher_name' => $a->relationLoaded('teacher') ? $a->teacher?->name : null,
            'created_at' => $a->created_at?->toIso8601String(),
        ];
    }
}
