<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileStudentHubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileStudentHubController extends Controller
{
    public function __construct(
        protected MobileStudentHubService $hubService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $data = $this->hubService->getStudentWidgets($request->user());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('pwa.student-mobile-hub', compact('data'));
    }
}
