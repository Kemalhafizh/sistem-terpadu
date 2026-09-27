<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\DocumentSignature;
use App\Services\Communication\DigitalSignatureService;
use Illuminate\Http\Request;

class DocumentSignatureController extends Controller
{
    /**
     * Display pending document signature requests queue.
     */
    public function pendingDocuments(DigitalSignatureService $signatureService)
    {
        $userId = auth()->id();
        $pendingSignatures = $signatureService->getPendingSignatures($userId);

        return view('documents.pending-signatures', compact('pendingSignatures'));
    }

    /**
     * Display document signature signing preview page.
     */
    public function signForm(int $id)
    {
        $userId = auth()->id();
        $signature = DocumentSignature::where('id', $id)
            ->where('signer_id', $userId)
            ->firstOrFail();

        return view('documents.sign', compact('signature'));
    }

    /**
     * Perform digital signature execution with E-Stamp cryptographic hashing.
     */
    public function signDocument(Request $request, int $id, DigitalSignatureService $signatureService)
    {
        $userId = auth()->id();
        $ipAddress = $request->ip();

        try {
            $signed = $signatureService->sign($id, $userId, $ipAddress);

            return redirect()->route('documents.pending_signatures')
                ->with('success', "Dokumen [{$signed->document_type} #{$signed->document_id}] berhasil ditandatangani secara digital (E-Stamp)!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membubuhi tanda tangan digital: '.$e->getMessage());
        }
    }

    /**
     * Public document verification endpoint (Accessible without authentication).
     */
    public function verifyPublic(string $code, DigitalSignatureService $signatureService)
    {
        $verification = $signatureService->verify($code);

        return view('documents.verify-public', compact('verification'));
    }

    /**
     * Display signature audit history log.
     */
    public function signatureHistory(DigitalSignatureService $signatureService)
    {
        $history = $signatureService->getSignatureHistory();

        return view('documents.history', compact('history'));
    }

    /**
     * Test trigger to create a signature request (Admin Panel).
     */
    public function testRequest(Request $request, DigitalSignatureService $signatureService)
    {
        $request->validate([
            'document_type' => 'required|string',
            'document_id' => 'required|integer',
            'signer_role' => 'required|string',
        ]);

        $signerId = auth()->id();
        $doc = $signatureService->requestSignature(
            $request->document_type,
            (int) $request->document_id,
            $signerId,
            $request->signer_role
        );

        return back()->with('success', "Permintaan tanda tangan digital baru [{$doc->qr_verification_code}] berhasil dibuat!");
    }
}
