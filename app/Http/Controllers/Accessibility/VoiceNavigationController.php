<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accessibility;

use App\Http\Controllers\Controller;
use App\Services\Accessibility\VoiceCommandProcessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoiceNavigationController extends Controller
{
    public function __construct(
        protected VoiceCommandProcessorService $voiceService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $locale = $request->query('lang', app()->getLocale());
        $commands = $this->voiceService->getActiveCommands((string) $locale);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'commands' => $commands]);
        }

        return view('accessibility.voice-command-helper', compact('commands'));
    }
}
