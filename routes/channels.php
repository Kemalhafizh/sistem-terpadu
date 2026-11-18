<?php

use App\Models\Exam;
use App\Models\ParentMessage;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('user.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{studentId}', function (User $user, int $studentId) {
    $student = Student::find($studentId);
    if (! $student) {
        return false;
    }

    // Parent of the student, the homeroom teacher, admin/superadmin, or —
    // covering BK/other staff a parent messaged who aren't the homeroom
    // teacher — anyone who already has a message thread about this student.
    return (int) $student->parent_id === (int) $user->id ||
           (int) $student->classroom?->teacher_id === (int) $user->id ||
           $user->role === 'admin' ||
           $user->role === 'superadmin' ||
           ParentMessage::where('student_id', $studentId)
               ->where(function ($q) use ($user) {
                   $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
               })
               ->exists();
});

Broadcast::channel('forum.thread.{threadId}', function (User $user, int $threadId) {
    return auth()->check();
});

Broadcast::channel('dashboard', function (User $user) {
    return in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah', 'waka_kesiswaan', 'waka_kurikulum']);
});

Broadcast::channel('cbt.exam.{examId}', function (User $user, int $examId) {
    $exam = Exam::find($examId);
    if (! $exam) {
        return false;
    }

    return (int) $exam->created_by === (int) $user->id ||
           in_array($user->role, ['admin', 'superadmin', 'waka_kurikulum', 'kepala_sekolah']);
});

// per-student proctor-intervention channel. Distinct from
// cbt.exam.{examId} above (proctor/admin only, also carries StudentCheatedEvent)
// — this is what lets the exam-taker themselves receive ProctorInterventionEvent
// (warnings, force-submit, extend-time, and per-student "broadcast to all"
// fan-out from CbtEnterpriseService::proctorBroadcastWarning()).
Broadcast::channel('cbt.exam.{examId}.student.{studentId}', function (User $user, int $examId, int $studentId) {
    $student = Student::find($studentId);
    if (! $student) {
        return false;
    }

    if ((int) $student->user_id === (int) $user->id) {
        return true;
    }

    $exam = Exam::find($examId);

    return $exam && (
        (int) $exam->created_by === (int) $user->id ||
        in_array($user->role, ['admin', 'superadmin', 'waka_kurikulum', 'kepala_sekolah'])
    );
});

Broadcast::channel('school.announcements', function (User $user) {
    return true; // Any authenticated user can listen
});

Broadcast::channel('campus.emergency', function (User $user) {
    return true; // Any authenticated user can listen to emergency alerts
});
