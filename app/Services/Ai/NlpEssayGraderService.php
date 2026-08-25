<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\EssaySubmission;
use App\Models\User;

class NlpEssayGraderService
{
    /**
     * Grade student essay submission and check for plagiarism.
     */
    public function gradeEssay(User $student, string $assignmentTitle, string $essayText): EssaySubmission
    {
        $words = str_word_count($essayText);
        $sentences = max(1, preg_match_all('/[.\?!]+/', $essayText));
        $avgWordsPerSentence = $words / $sentences;

        // 1. Argument Depth (vocabulary richness & length)
        $uniqueWords = count(array_unique(str_word_count(strtolower($essayText), 1)));
        $lexicalRichness = $words > 0 ? ($uniqueWords / $words) : 0;
        $argumentDepthScore = round(min(100.0, 40.0 + ($lexicalRichness * 40.0) + min(20.0, $words / 15.0)), 2);

        // 2. Coherence (structure & sentence length balance)
        $coherenceScore = round(max(50.0, 100.0 - abs($avgWordsPerSentence - 15) * 2.5), 2);

        // 3. Originality & Plagiarism check against existing submissions
        $plagiarismMatches = [];
        $maxSimilarity = 0.0;

        $previousEssays = EssaySubmission::where('id', '!=', 0)
            ->where('assignment_title', $assignmentTitle)
            ->limit(20)
            ->get();

        foreach ($previousEssays as $prev) {
            similar_text(strtolower($essayText), strtolower($prev->essay_text), $percent);
            if ($percent > 40.0) {
                $plagiarismMatches[] = [
                    'matched_submission_id' => $prev->id,
                    'similarity_percentage' => round($percent, 2),
                ];
                if ($percent > $maxSimilarity) {
                    $maxSimilarity = $percent;
                }
            }
        }

        $originalityScore = round(max(0.0, 100.0 - $maxSimilarity), 2);

        // Composite AI Score
        $compositeScore = round(
            ($argumentDepthScore * 0.4) +
            ($coherenceScore * 0.3) +
            ($originalityScore * 0.3),
            2
        );

        $feedback = "Esai menunjukkan pemahaman yang baik dengan kedalaman argumen {$argumentDepthScore}/100 dan koherensi struktur {$coherenceScore}/100. Skor orisinalitas adalah {$originalityScore}/100.";

        return EssaySubmission::create([
            'student_id' => $student->id,
            'assignment_title' => $assignmentTitle,
            'essay_text' => $essayText,
            'ai_score' => $compositeScore,
            'originality_score' => $originalityScore,
            'argument_depth_score' => $argumentDepthScore,
            'coherence_score' => $coherenceScore,
            'ai_feedback' => $feedback,
            'plagiarism_matches' => $plagiarismMatches,
        ]);
    }
}
