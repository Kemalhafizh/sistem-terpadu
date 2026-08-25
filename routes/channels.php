<?php

use App\Models\User;
use App\Models\Student;
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
    if (!$student) {
        return false;
    }
    
    // Wald kelas (teacher) or parent of the student can access the chat channel
    return (int) $student->parent_id === (int) $user->id || 
           (int) $student->classroom?->teacher_id === (int) $user->id ||
           $user->role === 'admin' ||
           $user->role === 'superadmin';
});

Broadcast::channel('forum.thread.{threadId}', function (User $user, int $threadId) {
    return auth()->check();
});

Broadcast::channel('dashboard', function (User $user) {
    return in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah', 'waka_kesiswaan', 'waka_kurikulum']);
});

Broadcast::channel('cbt.exam.{examId}', function (User $user, int $examId) {
    $exam = \App\Models\Exam::find($examId);
    if (!$exam) {
        return false;
    }
    return (int) $exam->created_by === (int) $user->id ||
           in_array($user->role, ['admin', 'superadmin', 'waka_kurikulum', 'kepala_sekolah']);
});

Broadcast::channel('school.announcements', function (User $user) {
    return true; // Any authenticated user can listen
});

Broadcast::channel('campus.emergency', function (User $user) {
    return true; // Any authenticated user can listen to emergency alerts
});
