<?php

namespace App\Services\Platform;

use App\Models\AiRecommendation;

class AiRecommendationService
{
    public function generateSuggestion(int $userId, string $type, string $text, float $confidence = 0.95): AiRecommendation
    {
        return AiRecommendation::create([
            'user_id' => $userId,
            'recommendation_type' => $type,
            'suggestion_text' => $text,
            'confidence_score' => $confidence,
            'status' => 'unread',
        ]);
    }
}
