<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accessibility;

use App\Http\Controllers\Controller;
use App\Models\LocalizationString;
use App\Services\Accessibility\DynamicLocalizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocalizationController extends Controller
{
    public function __construct(
        protected DynamicLocalizationService $locService
    ) {}

    public function index(): View
    {
        $strings = LocalizationString::orderBy('group_name')->get();
        if ($strings->isEmpty()) {
            $this->locService->updateString('navigation', 'dashboard', 'Dasbor Utama', 'Main Dashboard', 'لوحة القيادة');
            $this->locService->updateString('navigation', 'grades', 'Nilai & Rapor', 'Grades & Report', 'الدرجات والشهادات');
            $this->locService->updateString('navigation', 'finance', 'Keuangan & SPP', 'Finance & Fees', 'المالية والرسوم');
            $strings = LocalizationString::orderBy('group_name')->get();
        }

        return view('accessibility.translation-manager', compact('strings'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:50',
            'string_key' => 'required|string|max:100',
            'text_id' => 'required|string',
            'text_en' => 'nullable|string',
            'text_ar' => 'nullable|string',
        ]);

        $loc = $this->locService->updateString(
            $validated['group_name'],
            $validated['string_key'],
            $validated['text_id'],
            $validated['text_en'] ?? null,
            $validated['text_ar'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'String terjemahan berhasil disimpan.',
            'string' => $loc,
        ]);
    }
}
