<?php

namespace App\Services\HumanCapital;

use App\Models\Evaluation360Cycle;
use App\Models\Evaluation360Instrument;
use App\Models\Evaluation360Response;
use App\Models\Evaluation360Summary;

class Evaluation360Service
{
    public function createCycle(array $data, int $creatorId): Evaluation360Cycle
    {
        return Evaluation360Cycle::create([
            'title' => $data['title'],
            'academic_year' => $data['academic_year'] ?? '2026/2027',
            'semester' => $data['semester'] ?? 'ganjil',
            'status' => 'active',
            'start_date' => $data['start_date'] ?? now(),
            'end_date' => $data['end_date'] ?? now()->addMonth(),
            'created_by' => $creatorId,
        ]);
    }

    public function createInstrument(Evaluation360Cycle $cycle, array $data): Evaluation360Instrument
    {
        return Evaluation360Instrument::create([
            'cycle_id' => $cycle->id,
            'evaluator_type' => $data['evaluator_type'],
            'questions' => $data['questions'] ?? [
                'Penguasaan materi & pedagogik',
                'Kedisiplinan & ketepatan waktu',
                'Komunikasi & hubungan interpersonal',
                'Inovasi media & metode pembelajaran',
            ],
            'scale_type' => $data['scale_type'] ?? 'likert5',
            'is_anonymous' => $data['is_anonymous'] ?? true,
        ]);
    }

    public function submitResponse(Evaluation360Instrument $instrument, int $evaluateeId, ?int $evaluatorId, array $scores, ?string $comments = null): Evaluation360Response
    {
        return Evaluation360Response::create([
            'instrument_id' => $instrument->id,
            'evaluatee_id' => $evaluateeId,
            'evaluator_id' => $evaluatorId,
            'scores' => $scores,
            'comments' => $comments,
            'submitted_at' => now(),
        ]);
    }

    /** How much each perspective counts toward the overall score. */
    public const WEIGHTS = ['supervisor' => 0.3, 'peer' => 0.2, 'student' => 0.3, 'parent' => 0.1, 'self' => 0.1];

    /**
     * Average each perspective from the responses actually given. A
     * perspective nobody filled in stays null (it used to read 4.2, 4.1, 4.5,
     * 4.3 or 4.0), and the overall score weighs only the perspectives that
     * exist. Only numeric answers count: a student response also carries a
     * respondent hash, which used to be summed in and halve the rating.
     */
    public function generateSummary(int $cycleId, int $evaluateeId): Evaluation360Summary
    {
        $responses = Evaluation360Response::with('instrument')->whereHas('instrument', function ($q) use ($cycleId) {
            $q->where('cycle_id', $cycleId);
        })->where('evaluatee_id', $evaluateeId)->get();

        $byType = array_fill_keys(array_keys(self::WEIGHTS), []);
        foreach ($responses as $resp) {
            $numbers = array_values(array_filter(is_array($resp->scores) ? $resp->scores : [], 'is_numeric'));
            $type = $resp->instrument->evaluator_type;
            if ($numbers !== [] && array_key_exists($type, $byType)) {
                $byType[$type][] = array_sum($numbers) / count($numbers);
            }
        }

        $averages = array_map(fn (array $scores) => $scores === [] ? null : round(array_sum($scores) / count($scores), 2), $byType);
        $known = array_filter($averages, fn (?float $avg) => $avg !== null);
        $weight = array_sum(array_intersect_key(self::WEIGHTS, $known));
        $overall = $weight > 0
            ? round(array_sum(array_map(fn (string $type) => $known[$type] * self::WEIGHTS[$type], array_keys($known))) / $weight, 2)
            : null;

        return Evaluation360Summary::updateOrCreate(
            ['cycle_id' => $cycleId, 'evaluatee_id' => $evaluateeId],
            [
                'supervisor_avg' => $averages['supervisor'],
                'peer_avg' => $averages['peer'],
                'student_avg' => $averages['student'],
                'parent_avg' => $averages['parent'],
                'self_score' => $averages['self'],
                'overall_score' => $overall,
                // Written by people, not generated: the same two sentences used to be stored for every teacher.
                'strengths' => null,
                'improvements' => null,
                'generated_at' => now(),
            ]
        );
    }
}
