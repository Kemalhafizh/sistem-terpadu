<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileDashboardBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileTeacherDashboardController extends Controller
{
    public function __construct(
        protected MobileDashboardBuilderService $builderService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $data = $this->builderService->getTeacherWidgets($request->user());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('pwa.teacher-mobile-dashboard', compact('data'));
    }
}
