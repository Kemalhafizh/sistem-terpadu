<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOcrDocument;
use App\Models\DigitalSignature;
use App\Models\OcrResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentMobileApiController extends Controller
{
    /**
     * Daftar laporan raport / surat (dummy response as placeholders since real report tables might be complex)
     */
    public function reportList(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Rapor Semester Ganjil 2026',
                    'type' => 'academic_report',
                    'date' => '2026-12-15',
                ],
                [
                    'id' => 2,
                    'title' => 'Surat Pengantar Magang',
                    'type' => 'official_letter',
                    'date' => '2026-08-10',
                ]
            ]
        ]);
    }

    /**
     * Daftar sertifikat akademik (can use AchievementBadge/Certificate if we integrate)
     */
    public function certificateList(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 101,
                    'title' => 'Sertifikat Juara 1 OSN Matematika',
                    'issuer' => 'Kemdikbud',
                    'date' => '2026-05-20',
                ]
            ]
        ]);
    }

    /**
     * Upload foto untuk di-scan via OCR (queued job)
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

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah dan sedang diproses OCR.',
            'data' => [
                'task_id' => $ocrResult->id,
                'status' => $ocrResult->status,
            ]
        ], 202);
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

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan digital berhasil disimpan.',
            'data' => [
                'signature_id' => $signature->id,
                'signed_at' => $signature->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
