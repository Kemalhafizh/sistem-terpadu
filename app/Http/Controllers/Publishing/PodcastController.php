<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publishing;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Services\Publishing\PodcastDistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PodcastController extends Controller
{
    public function __construct(
        protected PodcastDistributionService $podcastService
    ) {}

    public function index(): View
    {
        $podcasts = Podcast::with(['host', 'episodes'])->latest()->get();

        return view('digital-publishing.podcast-directory', compact('podcasts'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'category' => 'required|in:islamic_studies,science,motivation,language',
            'cover_image_path' => 'nullable|string',
        ]);

        $podcast = $this->podcastService->createChannel($request->user(), $validated);

        return response()->json([
            'success' => true,
            'message' => 'Kanal podcast baru berhasil dibuat.',
            'podcast' => $podcast,
        ]);
    }
}
