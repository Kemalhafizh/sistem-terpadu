<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\ReportCardPdf;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentMobileApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Daftar rapor PDF yang sudah digenerate untuk siswa yang login (ReportCardPdf,
     * dibuat oleh ERaporPdfService::generateSinglePdf() — lihat ERaporApiController).
     * Hanya berarti untuk role student; role lain (belum ada konsep "rapor milik saya")
     * mendapat daftar kosong, bukan error.
     */
    public function reportList(Request $request)
    {
        $user = $request->user();
        if (! in_array($user->role, ['student', 'siswa'], true)) {
            return $this->successResponse([], 'Tidak ada rapor untuk peran ini.');
        }

        $pdfs = ReportCardPdf::with('template.academicYear')
            ->where('student_id', $user->id)
            ->latest('generated_at')
            ->get();

        $data = $pdfs->map(function (ReportCardPdf $pdf) {
            $template = $pdf->template;
            $year = $template?->academicYear;

            return [
                'id' => $pdf->id,
                'title' => $template?->name ?? 'Rapor',
                'type' => 'academic_report',
                'semester' => $template?->semester,
                'academic_year' => $year?->year,
                'date' => optional($pdf->generated_at)->format('Y-m-d'),
                'sha256_hash' => $pdf->sha256_hash,
                'verification_url' => $pdf->qr_verification_url,
                'file_url' => $pdf->file_path ? Storage::url($pdf->file_path) : null,
            ];
        });

        return $this->successResponse($data, 'Daftar rapor berhasil diambil');
    }

    /**
     * Daftar sertifikat akademik nyata — achievement siswa yang sudah tervalidasi
     * dan punya file sertifikat terunggah (Achievement::certificate_path). Achievement
     * tervalidasi tanpa file sertifikat (misal baru dicatat manual oleh guru) tidak
     * ikut tampil di sini karena tidak ada dokumen untuk diunduh.
     */
    public function certificateList(Request $request)
    {
        $user = $request->user();
        $student = $user->student;
        if (! $student) {
            return $this->successResponse([], 'Tidak ada sertifikat untuk peran ini.');
        }

        $achievements = Achievement::where('student_id', $student->id)
            ->validated()
            ->whereNotNull('certificate_path')
            ->latest('event_date')
            ->get();

        $data = $achievements->map(function (Achievement $a) {
            return [
                'id' => $a->id,
                'title' => $a->title,
                'issuer' => $a->organizer,
                'level' => strtoupper($a->level),
                'date' => $a->event_date ? $a->event_date->format('Y-m-d') : null,
                'file_url' => $a->certificate_path ? Storage::url($a->certificate_path) : null,
            ];
        });

        return $this->successResponse($data, 'Daftar sertifikat berhasil diambil');
    }
}
