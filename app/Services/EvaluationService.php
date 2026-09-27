<?php

namespace App\Services;

use App\Models\Evaluation360Cycle;
use App\Models\Evaluation360Instrument;
use App\Models\Evaluation360Response;
use App\Models\FacilityAsset;
use App\Models\FacilityEvaluation;
use App\Models\OsisCandidate;
use App\Models\OsisElection;
use App\Models\OsisVote;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    /**
     * Get list of teachers to be evaluated.
     */
    public function getTeachersToEvaluate()
    {
        // For simplicity, we just return all users with role 'guru' or 'teacher'
        // In a real scenario, we might only return teachers who teach the student.
        return User::whereIn('role', ['guru', 'teacher'])->get()->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'subject' => 'Guru Mata Pelajaran', // can be dynamic based on subjects
            ];
        });
    }

    /**
     * Submit an anonymous teacher evaluation.
     */
    public function submitTeacherEvaluation($evaluateeId, array $data, $evaluatorId = null)
    {
        return DB::transaction(function () use ($evaluateeId, $data, $evaluatorId) {
            $cycle = Evaluation360Cycle::where('status', 'active')->first();
            if (! $cycle) {
                throw new Exception('Tidak ada periode evaluasi yang aktif saat ini.');
            }

            $instrument = Evaluation360Instrument::where('cycle_id', $cycle->id)
                ->where('evaluator_type', 'student')
                ->first();

            if (! $instrument) {
                throw new Exception('Instrumen evaluasi untuk siswa tidak ditemukan.');
            }

            $respondentHash = $evaluatorId ? hash('sha256', $evaluatorId.'_'.$instrument->id.'_'.$evaluateeId.'_'.(config('app.key') ?: 'sista_eval_key')) : null;

            if ($evaluatorId) {
                $existingResponses = Evaluation360Response::where('instrument_id', $instrument->id)
                    ->where('evaluatee_id', $evaluateeId)
                    ->get();

                $alreadySubmitted = $existingResponses->contains(function ($resp) use ($evaluatorId, $respondentHash) {
                    if ($resp->evaluator_id === $evaluatorId) {
                        return true;
                    }
                    $scores = is_array($resp->scores) ? $resp->scores : [];

                    return isset($scores['respondent_hash']) && $scores['respondent_hash'] === $respondentHash;
                });

                if ($alreadySubmitted) {
                    throw new Exception('Anda sudah mengevaluasi guru ini.');
                }
            }

            $scorePayload = [
                'rating' => $data['rating'] ?? 5,
            ];
            if ($respondentHash) {
                $scorePayload['respondent_hash'] = $respondentHash;
            }

            return Evaluation360Response::create([
                'instrument_id' => $instrument->id,
                'evaluatee_id' => $evaluateeId,
                'evaluator_id' => $instrument->is_anonymous ? null : $evaluatorId,
                'scores' => $scorePayload,
                'comments' => $data['comments'] ?? null,
                'submitted_at' => now(),
            ]);
        });
    }

    /**
     * Get list of facilities.
     */
    public function getFacilities()
    {
        return FacilityAsset::all()->map(function ($facility) {
            return [
                'id' => $facility->id,
                'name' => $facility->name,
                'category' => $facility->category,
                'location' => $facility->location,
            ];
        });
    }

    /**
     * Submit a facility evaluation.
     */
    public function submitFacilityEvaluation($facilityId, array $data, $userId = null)
    {
        return FacilityEvaluation::create([
            'facility_asset_id' => $facilityId,
            'user_id' => $userId,
            'rating' => $data['rating'] ?? 5,
            'comments' => $data['comments'] ?? null,
        ]);
    }

    /**
     * Get active election info.
     */
    public function getActiveElection($studentId = null)
    {
        $election = OsisElection::where('status', 'active')
            ->with(['candidates.student.user', 'candidates.viceStudent.user'])
            ->first();
        if (! $election) {
            return null;
        }

        $votedCandidateId = $studentId
            ? OsisVote::where('election_id', $election->id)->where('voter_id', $studentId)->value('candidate_id')
            : null;

        return [
            'id' => $election->id,
            'title' => $election->title,
            'voting_start' => $election->voting_start->format('Y-m-d'),
            'voting_end' => $election->voting_end->format('Y-m-d'),
            'candidates' => $election->candidates->map(function ($candidate) use ($votedCandidateId) {
                return [
                    'id' => $candidate->id,
                    'candidate_number' => $candidate->candidate_number,
                    'president_name' => optional(optional($candidate->student)->user)->name,
                    'vice_president_name' => optional(optional($candidate->viceStudent)->user)->name,
                    'vision' => $candidate->vision,
                    'mission' => $candidate->mission,
                    'photo_url' => $candidate->photo_path ? asset('storage/'.$candidate->photo_path) : null,
                    'total_votes' => $candidate->vote_count,
                    'is_voted' => $votedCandidateId === $candidate->id,
                ];
            }),
        ];
    }

    /**
     * Cast an Osis vote.
     */
    public function castVote($electionId, $candidateId, $voterId)
    {
        return DB::transaction(function () use ($electionId, $candidateId, $voterId) {
            $election = OsisElection::find($electionId);
            if (! $election || $election->status !== 'active') {
                throw new Exception('Pemilihan tidak aktif atau tidak ditemukan.');
            }

            if (now()->format('Y-m-d') < $election->voting_start->format('Y-m-d') ||
                now()->format('Y-m-d') > $election->voting_end->format('Y-m-d')) {
                throw new Exception('Saat ini bukan masa voting.');
            }

            // Check if student already voted
            $exists = OsisVote::where('election_id', $electionId)
                ->where('voter_id', $voterId)
                ->exists();

            if ($exists) {
                throw new Exception('Anda sudah memberikan suara pada pemilihan ini (Double Vote Prevention).');
            }

            // Cast vote
            $vote = OsisVote::create([
                'election_id' => $electionId,
                'voter_id' => $voterId,
                'candidate_id' => $candidateId,
            ]);

            // Increment candidate vote count
            OsisCandidate::where('id', $candidateId)->increment('vote_count');

            return $vote;
        });
    }
}
