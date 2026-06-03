<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Student;
use App\Models\User;
use App\Exports\SurveyResponsesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SurveyAnalyticsService
{
    /**
     * Calculate response rate for the survey.
     */
    public function getResponseRate(Survey $survey): float
    {
        $responseCount = $survey->responses()->count();
        $potential = 0;

        if ($survey->target_classroom_id) {
            $potential = Student::where('classroom_id', $survey->target_classroom_id)->count();
        } elseif ($survey->target_role) {
            $potential = User::where('role', $survey->target_role)->count();
        } else {
            // Potential is all active users with standard roles (students, parents, gurus)
            $potential = User::whereIn('role', ['student', 'parent', 'guru'])->count();
        }

        if ($potential === 0) {
            return 0.0;
        }

        $rate = ($responseCount / $potential) * 100;
        return round(min($rate, 100.0), 2);
    }

    /**
     * Get aggregate statistics per question in the survey.
     */
    public function getAggregatedResults(Survey $survey): array
    {
        $survey->load('questions', 'responses');
        $responses = $survey->responses;
        $totalResponses = $responses->count();
        $aggregates = [];

        foreach ($survey->questions as $question) {
            $questionAnswers = [];
            foreach ($responses as $response) {
                $ans = $response->answers[$question->id] ?? null;
                if ($ans !== null) {
                    if (is_array($ans)) {
                        $questionAnswers = array_merge($questionAnswers, $ans);
                    } else {
                        $questionAnswers[] = $ans;
                    }
                }
            }

            $questionCount = count($questionAnswers);

            $summary = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'response_count' => $questionCount,
                'data' => []
            ];

            switch ($question->type) {
                case 'radio':
                case 'select':
                case 'checkbox':
                    $options = $question->options ?? [];
                    $counts = array_fill_keys($options, 0);

                    foreach ($questionAnswers as $ans) {
                        if (in_array($ans, $options)) {
                            $counts[$ans]++;
                        }
                    }

                    $breakdown = [];
                    foreach ($counts as $option => $count) {
                        $breakdown[] = [
                            'label' => $option,
                            'count' => $count,
                            'percentage' => $questionCount > 0 ? round(($count / $questionCount) * 100, 2) : 0
                        ];
                    }

                    $summary['data'] = $breakdown;
                    break;

                case 'scale':
                    $min = $question->scale_min ?? 1;
                    $max = $question->scale_max ?? 5;
                    $numericAnswers = array_filter($questionAnswers, 'is_numeric');
                    $numericCount = count($numericAnswers);

                    if ($numericCount > 0) {
                        $sum = array_sum($numericAnswers);
                        $average = round($sum / $numericCount, 2);

                        // Calculate Median
                        sort($numericAnswers);
                        $middle = floor($numericCount / 2);
                        if ($numericCount % 2 === 0) {
                            $median = ($numericAnswers[$middle - 1] + $numericAnswers[$middle]) / 2;
                        } else {
                            $median = $numericAnswers[$middle];
                        }

                        // Calculate Distribution
                        $distribution = [];
                        for ($i = $min; $i <= $max; $i++) {
                            $count = 0;
                            foreach ($numericAnswers as $ans) {
                                if ((int)$ans === $i) {
                                    $count++;
                                }
                            }
                            $distribution[$i] = [
                                'score' => $i,
                                'count' => $count,
                                'percentage' => round(($count / $numericCount) * 100, 2)
                            ];
                        }

                        $summary['data'] = [
                            'average' => $average,
                            'median' => $median,
                            'distribution' => $distribution,
                            'min_label' => $question->scale_min_label ?? 'Sangat Kurang',
                            'max_label' => $question->scale_max_label ?? 'Sangat Baik'
                        ];
                    } else {
                        $summary['data'] = [
                            'average' => 0,
                            'median' => 0,
                            'distribution' => []
                        ];
                    }
                    break;

                case 'text':
                case 'textarea':
                case 'date':
                    // Just list responses
                    $summary['data'] = array_slice($questionAnswers, 0, 50); // Limit to top 50
                    break;
            }

            $aggregates[$question->id] = $summary;
        }

        return $aggregates;
    }

    /**
     * Export survey analytics as formal A4 PDF.
     */
    public function exportResultsPdf(Survey $survey)
    {
        $survey->load('questions', 'responses');
        $analytics = $this->getAggregatedResults($survey);
        $responseRate = $this->getResponseRate($survey);

        $pdf = Pdf::loadView('surveys.results_report', compact('survey', 'analytics', 'responseRate'))
                  ->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Export raw survey responses as Excel.
     */
    public function exportResultsExcel(Survey $survey)
    {
        $filename = 'Respons_Survei_' . str_replace(' ', '_', $survey->title) . '_' . date('YmdHis') . '.xlsx';
        return Excel::download(new SurveyResponsesExport($survey), $filename);
    }
}
