<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publishing;

use App\Http\Controllers\Controller;
use App\Models\DataStory;
use App\Services\Publishing\DataStoryBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataStoryController extends Controller
{
    public function __construct(
        protected DataStoryBuilderService $storyService
    ) {}

    public function index(): View
    {
        $stories = DataStory::with('creator')->latest()->get();

        return view('digital-publishing.data-story-viewer', compact('stories'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'summary' => 'nullable|string',
            'story_configuration' => 'required|array',
            'story_id' => 'nullable|exists:data_stories,id',
        ]);

        $story = $this->storyService->saveStory(
            $request->user(),
            $validated,
            $validated['story_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan interaktif data story berhasil disimpan.',
            'story' => $story,
        ]);
    }
}
