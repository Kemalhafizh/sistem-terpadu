<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOcrDocument;
use App\Models\Achievement;
use App\Models\DigitalSignature;
use App\Models\OcrResult;
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

    /**
     * Upload foto untuk di-scan via OCR (queued job).
     *
     * The actual text extraction (ProcessOcrDocument job) is a simulated
     * placeholder, not a real OCR engine (Tesseract / Google Cloud Vision) —
     * see that job's own docblock. This endpoint and the polling one below are
     * real, working infrastructure around it; only the recognition itself is
     * a stand-in pending a real OCR provider integration.
     */
    public function ocrScan(Request $request)
    {
        $request->validate([
            'document_image' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120', // max 5MB (exclude SVG to prevent XSS)
            'document_type' => 'nullable|string|max:50',
        ]);

        $file = $request->file('document_image');
        $path = $file->store('documents/ocr', 'public');

        $ocrResult = OcrResult::create([
            'user_id' => $request->user()->id,
            'file_path' => $path,
            'document_type' => $request->document_type ?? 'unknown',
            'status' => 'pending',
        ]);

        // Dispatch background job for OCR
        ProcessOcrDocument::dispatch($ocrResult->id);

        return $this->successResponse([
            'task_id' => $ocrResult->id,
            'status' => $ocrResult->status,
        ], 'Dokumen berhasil diunggah dan sedang diproses OCR.', 202);
    }

    /**
     * Poll the status/result of a previously submitted OCR scan. The job runs
     * async (queued), so the client can't get extracted_text synchronously
     * from ocrScan() — it must poll this until status is "completed"/"failed".
     */
    public function ocrResult(Request $request, int $taskId)
    {
        $ocrResult = OcrResult::where('id', $taskId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $ocrResult) {
            return $this->errorResponse('Hasil OCR tidak ditemukan.', 404);
        }

        return $this->successResponse([
            'task_id' => $ocrResult->id,
            'status' => $ocrResult->status,
            'extracted_text' => $ocrResult->extracted_text,
        ], 'Status OCR berhasil diambil');
    }

    /**
     * Submit tanda tangan digital (base64)
     */
    public function submitSignature(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|max:50',
            'document_id' => 'required|integer',
            'signature_data' => 'required|string|max:500000', // max 500KB base64
        ]);

        $signature = DigitalSignature::create([
            'user_id' => $request->user()->id,
            'document_type' => $request->document_type,
            'document_id' => $request->document_id,
            'signature_data' => $request->signature_data,
            'ip_address' => $request->ip(),
        ]);

        return $this->successResponse([
            'signature_id' => $signature->id,
            'signed_at' => $signature->created_at->format('Y-m-d H:i:s'),
        ], 'Tanda tangan digital berhasil disimpan.');
    }
}
