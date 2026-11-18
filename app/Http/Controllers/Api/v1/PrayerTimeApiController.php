<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\Islamic\PrayerTimeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrayerTimeApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly PrayerTimeService $prayerTimeService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $schedule = $this->prayerTimeService->forSchool($validated['date'] ?? null);

        if ($schedule === null) {
            return $this->successResponse(
                null,
                __('Jadwal sholat belum tersedia karena koordinat sekolah belum diatur.')
            );
        }

        return $this->successResponse(
            $schedule,
            __('Jadwal sholat berhasil dimuat.')
        );
    }
}
