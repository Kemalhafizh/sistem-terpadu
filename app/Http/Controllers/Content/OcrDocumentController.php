<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\OcrDocument;
use App\Services\Content\OcrProcessingService;
use Illuminate\Http\Request;

class OcrDocumentController extends Controller
{
    /**
     * Display Intelligent Document Processing (IDP) OCR Console.
     */
    public function index()
    {
        $documents = OcrDocument::with('processor')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('content.ocr.upload', compact('documents'));
    }

    /**
     * Upload & Trigger IDP OCR extraction processing.
     */
    public function upload(Request $request, OcrProcessingService $ocrService)
    {
        $request->validate([
            'document_type' => 'required|in:ktp,kk,akta,ijazah,other',
            'file_name' => 'required|string|max:255',
        ]);

        $userId = auth()->id();
        $filePath = 'ocr_scans/'.time().'_'.$request->file_name;

        $doc = $ocrService->processDocument($filePath, $request->document_type, $userId);

        return redirect()->route('ocr.review', $doc->id)
            ->with('info', "Dokumen [{$doc->document_type}] tersimpan. Mesin OCR belum terpasang, jadi isi datanya secara manual dari berkas scan lalu verifikasi.");
    }

    /**
     * Side-by-side IDP verification review.
     */
    public function reviewExtraction(int $id, OcrProcessingService $ocrService)
    {
        $doc = OcrDocument::findOrFail($id);
        $mappedProfile = $ocrService->mapToStudentProfile($doc->id);

        return view('content.ocr.review-extraction', compact('doc', 'mappedProfile'));
    }

    /**
     * Save & Apply verified extracted data into registration profile.
     */
    public function applyToRegistration(Request $request, int $id, OcrProcessingService $ocrService)
    {
        $verifiedData = $request->input('extracted_data', []);
        $doc = $ocrService->validateExtraction($id, $verifiedData);

        return redirect()->route('ocr.index')
            ->with('success', "Hasil verifikasi dokumen OCR #{$doc->id} berhasil disimpan ke profil pendaftaran!");
    }
}
