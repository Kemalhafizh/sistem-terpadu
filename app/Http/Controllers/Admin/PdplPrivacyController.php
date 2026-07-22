<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DataAnonymizerService;
use App\Services\ImmutableAuditLedgerService;
use Illuminate\Http\Request;

class PdplPrivacyController extends Controller
{
    /**
     * Display UU PDP No. 27/2022 Data Privacy & Anonymization Dashboard.
     */
    public function index()
    {
        return view('admin.security.privacy');
    }

    /**
     * Preview PII Data Masking result.
     */
    public function anonymizePreview(Request $request, DataAnonymizerService $anonymizer)
    {
        $request->validate([
            'nik' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
        ]);

        $maskedNik = $request->nik ? $anonymizer->maskNik($request->nik) : null;
        $maskedPhone = $request->phone ? $anonymizer->maskPhone($request->phone) : null;
        $maskedEmail = $request->email ? $anonymizer->maskEmail($request->email) : null;

        return back()->with('preview_result', [
            'original_nik' => $request->nik,
            'masked_nik' => $maskedNik,
            'original_phone' => $request->phone,
            'masked_phone' => $maskedPhone,
            'original_email' => $request->email,
            'masked_email' => $maskedEmail,
        ]);
    }

    /**
     * Execute "Right to be Forgotten" protocol for a user.
     */
    public function executeRightToBeForgotten(
        Request $request,
        DataAnonymizerService $anonymizer,
        ImmutableAuditLedgerService $auditLedger
    ) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $success = $anonymizer->executeRightToBeForgotten($user);

        if ($success) {
            $auditLedger->record(
                'RIGHT_TO_BE_FORGOTTEN_EXECUTED',
                'App\Models\User',
                $user->id,
                $oldValues,
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => 'ANONYMIZED_PERMANENTLY',
                ]
            );

            return back()->with('success', "Protokol Hak untuk Dilupakan (Right to be Forgotten - UU PDP) berhasil dieksekusi untuk User #{$user->id}!");
        }

        return back()->with('error', "Gagal mengeksekusi protokol hak untuk dilupakan.");
    }
}
