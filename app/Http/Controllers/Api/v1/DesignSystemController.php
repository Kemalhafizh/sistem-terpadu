<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\DesignSystemService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class DesignSystemController extends Controller
{
    use ApiResponseTrait;

    protected DesignSystemService $designService;

    public function __construct(DesignSystemService $designService)
    {
        $this->designService = $designService;
    }

    /**
     * GET /api/v1/design/tokens
     */
    public function getTokens()
    {
        $tokens = $this->designService->getDesignTokens();

        return $this->successResponse($tokens, 'Design tokens retrieved successfully');
    }

    /**
     * GET /api/v1/design/theme
     */
    public function getUserTheme(Request $request)
    {
        $userId = $request->user() ? $request->user()->id : null;
        $theme = $this->designService->getUserTheme($userId);

        return $this->successResponse($theme, 'User theme payload retrieved');
    }

    /**
     * PUT /api/v1/design/theme/preferences
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'active_preset' => 'nullable|string',
            'custom_overrides' => 'nullable|array',
            'auto_theme' => 'nullable|boolean',
            'dynamic_color' => 'nullable|boolean',
        ]);

        $userId = $request->user()->id;
        $pref = $this->designService->updatePreferences($userId, $request->all());

        return $this->successResponse($pref, 'Theme preferences updated successfully');
    }

    /**
     * GET /api/v1/design/presets
     */
    public function getPresets()
    {
        $active = $this->designService->getActivePreset();
        $contextual = $this->designService->getContextualPreset();

        return $this->successResponse([
            'active_preset' => $active,
            'contextual_preset' => $contextual,
            'available_presets' => [
                ['slug' => 'default', 'name' => 'Sultan Agung Green'],
                ['slug' => 'night_study', 'name' => 'Night Study Dark'],
                ['slug' => 'exam_mode', 'name' => 'Exam Mode Alert'],
                ['slug' => 'ramadhan_gold', 'name' => 'Ramadhan Gold'],
            ],
        ], 'Theme presets retrieved successfully');
    }
}
