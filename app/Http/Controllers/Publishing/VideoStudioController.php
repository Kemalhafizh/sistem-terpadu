<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publishing;

use App\Http\Controllers\Controller;
use App\Models\VideoStudio;
use App\Services\Publishing\VideoTranscodingOrchestratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoStudioController extends Controller
{
    public function __construct(
        protected VideoTranscodingOrchestratorService $videoService
    ) {}

    public function index(): View
    {
        $videos = VideoStudio::with('uploader')->latest()->get();

        return view('digital-publishing.video-streaming-player', compact('videos'));
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'original_file_path' => 'nullable|string',
            'duration_seconds' => 'nullable|integer',
            'thumbnail_path' => 'nullable|string',
        ]);

        $video = $this->videoService->registerVideo($request->user(), $validated);

        return response()->json([
            'success' => true,
            'message' => 'Video berhasil diunggah dan siap di-streaming secara adaptif.',
            'video' => $video,
        ]);
    }
}
