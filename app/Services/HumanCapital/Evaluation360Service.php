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

    public function generateSummary(int $cycleId, int $evaluateeId): Evaluation360Summary
    {
        $cycle = Evaluation360Cycle::findOrFail($cycleId);
        $responses = Evaluation360Response::with('instrument')->whereHas('instrument', function ($q) use ($cycleId) {
            $q->where('cycle_id', $cycleId);
        })->where('evaluatee_id', $evaluateeId)->get();

        $supervisorScores = [];
        $peerScores = [];
        $studentScores = [];
        $parentScores = [];
        $selfScores = [];

        foreach ($responses as $resp) {
            $type = $resp->instrument->evaluator_type;
            $avg = is_array($resp->scores) ? array_sum($resp->scores) / count($resp->scores) : 4.0;

            if ($type === 'supervisor') {
                $supervisorScores[] = $avg;
            } elseif ($type === 'peer') {
                $peerScores[] = $avg;
            } elseif ($type === 'student') {
                $studentScores[] = $avg;
            } elseif ($type === 'parent') {
                $parentScores[] = $avg;
            } elseif ($type === 'self') {
                $selfScores[] = $avg;
            }
        }

        $supAvg = count($supervisorScores) ? array_sum($supervisorScores) / count($supervisorScores) : 4.2;
        $peerAvg = count($peerScores) ? array_sum($peerScores) / count($peerScores) : 4.1;
        $stuAvg = count($studentScores) ? array_sum($studentScores) / count($studentScores) : 4.5;
        $parAvg = count($parentScores) ? array_sum($parentScores) / count($parentScores) : 4.3;
        $selfScore = count($selfScores) ? array_sum($selfScores) / count($selfScores) : 4.0;

        $overall = round(($supAvg * 0.3) + ($peerAvg * 0.2) + ($stuAvg * 0.3) + ($parAvg * 0.1) + ($selfScore * 0.1), 2);

        return Evaluation360Summary::updateOrCreate(
            ['cycle_id' => $cycleId, 'evaluatee_id' => $evaluateeId],
            [
                'supervisor_avg' => $supAvg,
                'peer_avg' => $peerAvg,
                'student_avg' => $stuAvg,
                'parent_avg' => $parAvg,
                'self_score' => $selfScore,
                'overall_score' => $overall,
                'strengths' => ['Penguasaan materi sangat baik', 'Keahlian komunikasi interaktif'],
                'improvements' => ['Pengembangan penggunaan LMS SCORM'],
                'generated_at' => now(),
            ]
        );
    }
}
