<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MobileV2ApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/v1/ai/tutor/suggestions
     */
    public function getAiTutorSuggestions(Request $request)
    {
        return $this->successResponse([
            'Jelaskan konsep Limit Trigonometri dengan cara mudah dipahami',
            'Bagaimana hukum bacaan Mad Lazim Mukhaffaf Kilmi?',
            'Bantu saya membuat kerangka esai tentang Peradaban Islam Andalusia',
            'Berikan contoh soal UTBK Penalaran Matematika dan pembahasannya',
        ], 'Rekomendasi prompt AI Tutor berhasil dimuat');
    }
}
