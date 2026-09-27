<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\InteractionConfigService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    use ApiResponseTrait;

    protected InteractionConfigService $configService;

    public function __construct(InteractionConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * GET /api/v1/config/animations
     */
    public function getAnimations(Request $request)
    {
        $userId = $request->user()->id;
        $config = $this->configService->getAnimationConfig($userId);

        return $this->successResponse($config, 'Animation configuration retrieved successfully');
    }

    /**
     * PUT /api/v1/config/animations/preferences
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'reduce_motion' => 'nullable|boolean',
            'haptic_level' => 'nullable|in:off,light,medium,strong',
            'skeleton_loading' => 'nullable|boolean',
            'spring_animations' => 'nullable|boolean',
            'parallax_effects' => 'nullable|boolean',
            'page_transitions' => 'nullable|in:none,fade,slide,shared_element',
            'pull_refresh_style' => 'nullable|in:classic,physics,lottie',
        ]);

        $userId = $request->user()->id;
        $pref = $this->configService->updatePreferences($userId, $request->all());

        return $this->successResponse($pref, 'Animation preferences updated successfully');
    }
}
