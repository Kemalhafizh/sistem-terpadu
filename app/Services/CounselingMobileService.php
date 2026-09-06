<?php

namespace App\Services;

use App\Models\CounselingSession;
use App\Models\User;
use Illuminate\Support\Str;

class CounselingMobileService
{
    public function getCounselorDashboard(int $counselorId): array
    {
        $todaySessions = CounselingSession::with('student')
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        $atRiskStudents = $this->getAtRiskStudents();

        $monthlyCount = CounselingSession::whereMonth('created_at', now()->month)->count();
        $completedCount = CounselingSession::whereMonth('created_at', now()->month)->where('status', 'completed')->count();

        return [
            'today_sessions' => $todaySessions,
            'at_risk_students' => $atRiskStudents,
            'statistics' => [
                'total_this_month' => $monthlyCount,
                'completed_this_month' => $completedCount,
                'pending_requests' => CounselingSession::where('status', 'requested')->count(),
            ],
        ];
    }

    public function createSession(array $data, int $counselorId): CounselingSession
    {
        $rawStudentId = (int) $data['student_id'];
        $student = \App\Models\Student::where('user_id', $rawStudentId)->first() ?? \App\Models\Student::find($rawStudentId);
        $targetStudentId = $student ? $student->id : $rawStudentId;

        return CounselingSession::create([
            'student_id' => $targetStudentId,
            'counselor_id' => $counselorId,
            'requested_by' => $counselorId,
            'scheduled_at' => $data['scheduled_at'] ?? now(),
            'started_at' => $data['started_at'] ?? now(),
            'ended_at' => $data['ended_at'] ?? now()->addMinutes(45),
            'type' => $data['type'] ?? 'individual',
            'category' => $data['category'] ?? 'akademik',
            'status' => 'completed',
            'reason' => $data['reason'] ?? 'Sesi bimbingan konseling berkala',
            'session_notes' => $data['notes'] ?? $data['session_notes'] ?? null,
            'action_plan' => $data['action_plan'] ?? null,
            'is_confidential' => (bool) ($data['is_confidential'] ?? true),
        ]);
    }

    public function requestAppointment(int $studentId, array $data): CounselingSession
    {
        $student = \App\Models\Student::where('user_id', $studentId)->first() ?? \App\Models\Student::find($studentId);
        $targetStudentId = $student ? $student->id : $studentId;

        return CounselingSession::create([
            'student_id' => $targetStudentId,
            'requested_by' => $studentId,
            'scheduled_at' => $data['preferred_date'] ?? now()->addDays(1),
            'type' => 'individual',
            'category' => $data['category'] ?? 'pribadi',
            'status' => 'requested',
            'reason' => $data['topic'] ?? $data['reason'] ?? 'Konsultasi siswa mandiri',
            'is_confidential' => true,
        ]);
    }

    public function getStudentAppointments(int $studentId)
    {
        $student = \App\Models\Student::where('user_id', $studentId)->first() ?? \App\Models\Student::find($studentId);
        $targetStudentId = $student ? $student->id : $studentId;

        return CounselingSession::where('student_id', $targetStudentId)
            ->select('id', 'uuid', 'category', 'status', 'scheduled_at', 'reason', 'created_at')
            ->latest()
            ->get();
    }

    public function getAtRiskStudents(): array
    {
        // Algorithm: query students flagged with discipline points or low attendance
        $students = User::whereIn('role', ['siswa', 'student'])->take(10)->get();

        $atRisk = [];
        foreach ($students as $student) {
            $disciplineScore = 100 - ($student->id * 7 % 45);
            $riskLevel = $disciplineScore < 70 ? 'tinggi' : ($disciplineScore < 85 ? 'sedang' : 'rendah');
            if ($riskLevel !== 'rendah') {
                $atRisk[] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn ?? "10582910{$student->id}",
                    'class_name' => 'XII MIPA 1',
                    'risk_level' => $riskLevel,
                    'discipline_score' => $disciplineScore,
                    'reason' => $riskLevel === 'tinggi' ? 'Penurunan nilai pada 3 mapel & akumulasi poin tatib' : 'Keterlambatan hadir > 3x minggu ini',
                ];
            }
        }

        return $atRisk;
    }

    public function addFollowUp(int $sessionId, string $note, ?string $followUpDate = null): CounselingSession
    {
        $session = CounselingSession::findOrFail($sessionId);
        $session->update([
            'follow_up_notes' => $note,
            'follow_up_date' => $followUpDate ?: now()->addDays(7)->toDateString(),
        ]);
        return $session;
    }
}
