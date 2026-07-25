<?php

namespace App\Services\Grc;

use App\Models\Incident;
use App\Models\RiskRegister;
use Illuminate\Support\Collection;

class RiskManagementService
{
    /**
     * Register new Enterprise Risk item.
     */
    public function assessRisk(array $data, int $ownerId): RiskRegister
    {
        return RiskRegister::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'] ?? 'Safety',
            'likelihood' => $data['likelihood'] ?? 'possible',
            'impact' => $data['impact'] ?? 'moderate',
            'mitigation_plan' => $data['mitigation_plan'] ?? null,
            'owner_id' => $ownerId,
            'status' => $data['status'] ?? 'identified',
            'review_date' => $data['review_date'] ?? now()->addMonths(6)->toDateString(),
        ]);
    }

    /**
     * Calculate numeric risk score (1-25) and level.
     */
    public function calculateRiskScore(string $likelihood, string $impact): array
    {
        $dummyRisk = new RiskRegister(['likelihood' => $likelihood, 'impact' => $impact]);
        $score = $dummyRisk->risk_score;
        $level = $dummyRisk->risk_level;

        return [
            'likelihood' => $likelihood,
            'impact' => $impact,
            'score' => $score,
            'level' => $level,
        ];
    }

    /**
     * Report new operational or GRC incident.
     */
    public function createIncident(array $data, int $reportedBy): Incident
    {
        return Incident::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'] ?? 'Safety',
            'severity' => $data['severity'] ?? 'medium',
            'reported_by' => $reportedBy,
            'assigned_to' => $data['assigned_to'] ?? null,
            'status' => 'reported',
            'reported_at' => now(),
        ]);
    }

    /**
     * Resolve incident and log resolution details.
     */
    public function resolveIncident(int $incidentId, string $notes, ?int $assignedTo = null): Incident
    {
        $incident = Incident::findOrFail($incidentId);
        $incident->status = 'resolved';
        $incident->resolution_notes = $notes;
        if ($assignedTo) {
            $incident->assigned_to = $assignedTo;
        }
        $incident->resolved_at = now();
        $incident->save();

        return $incident;
    }

    /**
     * Generate 5x5 ERM Risk Matrix Heatmap.
     */
    public function generateRiskMatrix(): array
    {
        $likelihoods = ['almost_certain', 'likely', 'possible', 'unlikely', 'rare'];
        $impacts = ['insignificant', 'minor', 'moderate', 'major', 'catastrophic'];

        $risks = RiskRegister::with('owner')->get();
        $matrix = [];

        foreach ($likelihoods as $l) {
            foreach ($impacts as $i) {
                $filtered = $risks->filter(fn ($r) => $r->likelihood === $l && $r->impact === $i);
                $calc = $this->calculateRiskScore($l, $i);

                $matrix[$l][$i] = [
                    'score' => $calc['score'],
                    'level' => $calc['level'],
                    'risks' => $filtered->values(),
                ];
            }
        }

        return [
            'likelihoods' => $likelihoods,
            'impacts' => $impacts,
            'matrix' => $matrix,
            'total_risks' => $risks->count(),
        ];
    }
}
