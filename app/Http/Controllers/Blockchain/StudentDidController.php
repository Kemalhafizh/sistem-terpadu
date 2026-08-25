<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blockchain;

use App\Http\Controllers\Controller;
use App\Services\Blockchain\StudentDidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDidController extends Controller
{
    public function __construct(
        protected StudentDidService $didService
    ) {}

    public function getMyDid(Request $request): JsonResponse
    {
        $did = $this->didService->getOrCreateDid($request->user());

        return response()->json([
            'success' => true,
            'did' => $did,
        ], 200);
    }

    public function getPassport(Request $request): JsonResponse
    {
        $passport = $this->didService->generateVerifiablePresentation($request->user());

        return response()->json([
            'success' => true,
            'passport' => $passport,
        ], 200);
    }
}
