<?php

declare(strict_types=1);

namespace App\Services\InteractiveUi;

use App\Models\User;
use App\Models\WhiteboardSession;
use Illuminate\Support\Str;

class CollaborativeWhiteboardService
{
    /**
     * Start a new real-time whiteboard session
     */
    public function startSession(User $host, string $title): WhiteboardSession
    {
        return WhiteboardSession::create([
            'session_id' => (string) Str::uuid(),
            'title' => $title,
            'host_id' => $host->id,
            'status' => 'active',
            'canvas_state_json' => json_encode(['objects' => [], 'background' => '#ffffff']),
        ]);
    }

    /**
     * Save canvas snapshot / state
     */
    public function saveSnapshot(WhiteboardSession $session, string $canvasJson, ?string $imagePath = null): bool
    {
        return $session->update([
            'canvas_state_json' => $canvasJson,
            'snapshot_image_path' => $imagePath ?? $session->snapshot_image_path,
        ]);
    }
}
