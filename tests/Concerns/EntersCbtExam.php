<?php

namespace Tests\Concerns;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\CbtAccessControlService;
use Laravel\Sanctum\Sanctum;

/**
 * Enters a CBT exam the way the Android app does: the proctor's token is
 * issued, then the student posts it to validate-token. Only after that do the
 * questions, decryption-key, micro-sync, upload-essay and submit endpoints
 * accept the student (ExamAttempt::openFor).
 */
trait EntersCbtExam
{
    protected function enterCbtExam(User $studentUser, Exam $exam): ExamAttempt
    {
        if (empty($exam->access_token) || $exam->isTokenExpired()) {
            app(CbtAccessControlService::class)->generateToken($exam);
        }
        $exam->refresh();

        Sanctum::actingAs($studentUser);
        $this->withHeaders(['X-SISTA-Platform' => 'android'])
            ->postJson("/api/v1/student/cbt/exams/{$exam->id}/validate-token", ['token' => $exam->access_token])
            ->assertOk();

        return ExamAttempt::openFor($exam, $studentUser->student);
    }
}
