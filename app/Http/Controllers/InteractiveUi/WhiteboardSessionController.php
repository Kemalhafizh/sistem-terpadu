<?php

declare(strict_types=1);

namespace App\Http\Controllers\InteractiveUi;

use App\Http\Controllers\Controller;
use App\Models\WhiteboardSession;
use App\Services\InteractiveUi\CollaborativeWhiteboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhiteboardSessionController extends Controller
{
    public function __construct(
        protected CollaborativeWhiteboardService $whiteboardService
    ) {}

    public function index(): View
    {
        $sessions = WhiteboardSession::with('host')->latest()->get();

        return view('interactive-ui.collaborative-whiteboard', compact('sessions'));
    }

    public function createSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
        ]);

        $session = $this->whiteboardService->startSession($request->user(), $validated['title']);

        return response()->json([
            'success' => true,
            'message' => 'Sesi papan tulis interaktif berhasil dibuat.',
            'session' => $session,
        ]);
    }

    public function saveCanvasState(Request $request, WhiteboardSession $session): JsonResponse
    {
        abort_unless((int) $session->host_id === $request->user()->id || $request->user()->role === 'superadmin', 403);

        $validated = $request->validate([
            'canvas_state_json' => 'required|string',
            'snapshot_image_path' => 'nullable|string',
        ]);

        $saved = $this->whiteboardService->saveSnapshot(
            $session,
            $validated['canvas_state_json'],
            $validated['snapshot_image_path'] ?? null
        );

        return response()->json([
            'success' => $saved,
            'message' => 'Coretan papan tulis berhasil disimpan ke cloud.',
        ]);
    }
}
