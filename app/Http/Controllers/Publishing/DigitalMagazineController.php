<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publishing;

use App\Http\Controllers\Controller;
use App\Models\DigitalMagazine;
use App\Services\Publishing\DigitalMagazinePublisherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DigitalMagazineController extends Controller
{
    public function __construct(
        protected DigitalMagazinePublisherService $magazineService
    ) {}

    public function index(): View
    {
        $magazines = DigitalMagazine::with('author')->latest()->get();

        return view('digital-publishing.magazine-block-editor', compact('magazines'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'issue_number' => 'required|integer|min:1',
            'content_blocks' => 'required|array',
            'cover_image_path' => 'nullable|string',
            'magazine_id' => 'nullable|exists:digital_magazines,id',
        ]);

        $magazine = $this->magazineService->saveMagazine(
            $request->user(),
            $validated,
            $validated['magazine_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Edisi majalah digital berhasil diterbitkan.',
            'magazine' => $magazine,
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:191',
        ]);

        $sub = $this->magazineService->subscribe($validated['email'], $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih, Anda telah berlangganan majalah digital & buletin sekolah.',
            'subscriber' => $sub,
        ]);
    }
}
