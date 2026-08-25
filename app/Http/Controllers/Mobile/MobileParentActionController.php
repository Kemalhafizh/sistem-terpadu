<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Mobile\ParentQuickActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileParentActionController extends Controller
{
    public function __construct(
        protected ParentQuickActionService $parentService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $data = $this->parentService->getParentSummary($request->user());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('pwa.parent-quick-actions', compact('data'));
    }

    public function reportAbsence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'status' => 'required|in:sakit,izin',
            'reason' => 'required|string|max:500',
            'attachment_url' => 'nullable|string|url',
        ]);

        $res = $this->parentService->reportAbsence(
            (int) $validated['student_id'],
            $validated['status'],
            $validated['reason'],
            $validated['attachment_url'] ?? null
        );

        return response()->json($res);
    }
}
