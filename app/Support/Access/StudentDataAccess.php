<?php

namespace App\Support\Access;

use App\Models\ElearningClass;
use App\Models\Student;
use App\Models\User;

/**
 * Who may see or act on a student's academic data, in one place:
 * the student themself, their parent, and school staff. Teachers acting on
 * a class (grading, entering scores) must own that class or assessment.
 * Superadmin passes everything.
 */
class StudentDataAccess
{
    public const STUDENT_ROLES = ['student', 'siswa'];

    /** Every spelling of "parent" the school's accounts use. */
    public const PARENT_ROLES = ['parent', 'orang_tua', 'orangtua', 'wali_murid'];

    public const TEACHER_ROLES = ['guru', 'teacher'];

    /** Staff who may look up any student's academic record. */
    public const STAFF_ROLES = [
        'guru', 'teacher', 'bk', 'wali_kelas',
        'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'staf_tu', 'admin',
    ];

    /**
     * May [viewer] see the academic data of the student whose *user* id is
     * [studentUserId]? (Rapor tables key students by users.id.)
     */
    public static function canViewStudentUser(User $viewer, int $studentUserId): bool
    {
        if ($viewer->role === 'superadmin' || in_array($viewer->role, self::STAFF_ROLES, true)) {
            return true;
        }
        if (in_array($viewer->role, self::STUDENT_ROLES, true)) {
            return $viewer->id === $studentUserId;
        }
        if (in_array($viewer->role, self::PARENT_ROLES, true)) {
            return Student::where('user_id', $studentUserId)->where('parent_id', $viewer->id)->exists();
        }

        return false;
    }

    public static function authorizeStudentUser(User $viewer, int $studentUserId): void
    {
        if (! self::canViewStudentUser($viewer, $studentUserId)) {
            abort(403, __('Akses ditolak. Anda tidak berhak melihat data siswa ini.'));
        }
    }

    /**
     * 404 unless [user] may see [student] (Student::visibleTo). A record of a
     * student outside the caller's scope is treated as not found.
     */
    public static function authorizeStudentRecord(User $user, ?Student $student): void
    {
        abort_unless($student !== null && $student->isVisibleTo($user), 404);
    }

    /** A teacher may only change what belongs to their own class. */
    public static function authorizeTeacherOwns(User $teacher, ?int $ownerId): void
    {
        if ($teacher->role !== 'superadmin' && $ownerId !== $teacher->id) {
            abort(403, __('Akses ditolak. Data ini milik kelas guru lain.'));
        }
    }

    /** Students see classes of their own classroom; teachers their own classes. */
    public static function authorizeClassMember(User $user, ElearningClass $class): void
    {
        if ($user->role === 'superadmin') {
            return;
        }
        if (in_array($user->role, self::TEACHER_ROLES, true)) {
            self::authorizeTeacherOwns($user, $class->teacher_id);

            return;
        }
        if (in_array($user->role, self::STUDENT_ROLES, true) && $user->student?->classroom_id === $class->classroom_id) {
            return;
        }
        abort(403, __('Akses ditolak. Anda bukan anggota kelas ini.'));
    }
}
