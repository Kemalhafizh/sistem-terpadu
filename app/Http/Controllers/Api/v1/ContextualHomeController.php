<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ContextualHomeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ContextualHomeController extends Controller
{
    use ApiResponseTrait;

    protected ContextualHomeService $homeService;

    public function __construct(ContextualHomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    /**
     * GET /api/v1/home/contextual
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $payload = $this->homeService->getHomePayload($user);

        return $this->successResponse($payload, 'Contextual home data retrieved successfully');
    }

    /**
     * GET /api/v1/home/suggestions
     */
    public function suggestions(Request $request)
    {
        $user = $request->user();
        $suggestions = $this->homeService->getSmartSuggestions($user);

        return $this->successResponse($suggestions, 'Smart suggestions retrieved successfully');
    }

    /**
     * POST /api/v1/home/dismiss-suggestion OR POST /api/v1/home/suggestions/{id}/dismiss
     */
    public function dismissSuggestion(Request $request, ?string $id = null)
    {
        $suggestionId = $id ?: $request->input('id');
        if ($suggestionId) {
            $this->homeService->dismissSuggestion($request->user()->id, $suggestionId);
        }

        return $this->successResponse(null, 'Suggestion dismissed successfully');
    }
}
