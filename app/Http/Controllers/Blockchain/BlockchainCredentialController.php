<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blockchain;

use App\Http\Controllers\Controller;
use App\Models\BlockchainCredential;
use App\Models\User;
use App\Services\Blockchain\BlockchainCredentialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockchainCredentialController extends Controller
{
    public function __construct(
        protected BlockchainCredentialService $credentialService
    ) {}

    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'credential_type' => 'required|string|in:diploma,transcript,achievement,tahfidz_sanad',
            'certificate_number' => 'required|string|unique:blockchain_credentials,certificate_number',
            'metadata' => 'required|array',
        ]);

        // The school issues to a student (or alumnus); never to itself.
        $recipient = User::findOrFail($validated['student_id']);
        if (! in_array($recipient->role, ['student', 'siswa', 'alumni'], true)) {
            abort(422, 'Kredensial hanya dapat diterbitkan untuk siswa atau alumni.');
        }

        $credential = $this->credentialService->issueCredential(
            $recipient,
            $validated['credential_type'],
            $validated['certificate_number'],
            $validated['metadata']
        );

        return response()->json([
            'success' => true,
            'credential' => $credential,
        ], 201);
    }

    public function myCredentials(Request $request): JsonResponse
    {
        $credentials = BlockchainCredential::where('student_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $credentials,
        ], 200);
    }

    public function verifyPublic(string $certificateNumber): JsonResponse
    {
        $result = $this->credentialService->verifyCredential($certificateNumber);

        $status = $result['verified'] ? 200 : 404;

        return response()->json($result, $status);
    }
}
