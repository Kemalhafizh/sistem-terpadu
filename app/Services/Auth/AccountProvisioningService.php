<?php

namespace App\Services\Auth;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\Classroom;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use App\Support\Access\StudentDataAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountProvisioningService
{
    private const TEMP_PASSWORD_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(
        protected UserIdentifierResolver $identifierResolver
    ) {}

    /**
     * Generate a random unambiguous temporary password (never hardcoded).
     */
    public function generateTemporaryPassword(int $length = 8): string
    {
        $maxIndex = strlen(self::TEMP_PASSWORD_ALPHABET) - 1;
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= self::TEMP_PASSWORD_ALPHABET[random_int(0, $maxIndex)];
        }

        return $password;
    }

    /**
     * Create a student user account when none exists yet (optional email supported).
     *
     * @return array{user: User, plain_password: ?string, created: bool}
     */
    public function ensureStudentUser(string $name, ?string $email = null, ?string $phoneNumber = null): array
    {
        $cleanEmail = $this->sanitizeEmail($email);
        $cleanPhone = $this->identifierResolver->normalizeLocalPhone($phoneNumber);

        if ($cleanEmail !== null) {
            $existing = User::where('email', $cleanEmail)->first();
            if ($existing) {
                return ['user' => $existing, 'plain_password' => null, 'created' => false];
            }
        }

        $plainPassword = $this->generateTemporaryPassword();
        $user = User::create([
            'name' => trim($name),
            'email' => $cleanEmail,
            'phone_number' => $cleanPhone,
            'password' => Hash::make($plainPassword),
            'role' => 'student',
            'account_type' => 'siswa',
            'must_change_password' => true,
        ]);

        return ['user' => $user, 'plain_password' => $plainPassword, 'created' => true];
    }

    /**
     * Link or provision ONE shared parent account (`orang_tua`, username = phone number)
     * across siblings sharing the same parent phone/email.
     *
     * @return array{user: User, plain_password: ?string, created: bool}|null
     */
    public function ensureParentForStudent(
        Student $student,
        ?string $parentName = null,
        ?string $parentPhone = null,
        ?string $parentEmail = null,
    ): ?array {
        if ($student->parent_id !== null) {
            $existingParent = User::find($student->parent_id);
            if ($existingParent) {
                return ['user' => $existingParent, 'plain_password' => null, 'created' => false];
            }
        }

        $canonicalPhone = $this->identifierResolver->normalizeLocalPhone($parentPhone);
        $cleanEmail = $this->sanitizeEmail($parentEmail);
        if ($canonicalPhone === null && $cleanEmail === null) {
            return null;
        }

        $phoneVariants = $parentPhone ? $this->identifierResolver->phoneVariants($parentPhone) : [];
        $parent = null;

        if ($phoneVariants !== []) {
            $parent = User::whereIn('phone_number', $phoneVariants)
                ->whereIn('role', StudentDataAccess::PARENT_ROLES)
                ->first();
        }

        if (! $parent && $cleanEmail !== null) {
            $parent = User::where('email', $cleanEmail)
                ->whereIn('role', StudentDataAccess::PARENT_ROLES)
                ->first();
        }

        if ($parent) {
            $updates = [];
            if (blank($parent->phone_number) && $canonicalPhone !== null) {
                $updates['phone_number'] = $canonicalPhone;
            }
            if ($updates !== []) {
                $parent->update($updates);
            }

            $student->update(['parent_id' => $parent->id]);

            return ['user' => $parent, 'plain_password' => null, 'created' => false];
        }

        if ($cleanEmail !== null && User::where('email', $cleanEmail)->exists()) {
            $cleanEmail = null;
        }

        $plainPassword = $this->generateTemporaryPassword();
        $displayName = filled($parentName)
            ? trim($parentName)
            : 'Wali '.($student->user?->name ?? 'Siswa '.$student->nis);

        $parent = User::create([
            'name' => $displayName,
            'email' => $cleanEmail,
            'phone_number' => $canonicalPhone,
            'password' => Hash::make($plainPassword),
            'role' => 'parent',
            'account_type' => 'orang_tua',
            'must_change_password' => true,
        ]);

        $student->update(['parent_id' => $parent->id]);

        return ['user' => $parent, 'plain_password' => $plainPassword, 'created' => true];
    }

    /**
     * Bulk provision missing parent accounts from linked SPMB registrations.
     *
     * @return list<array{student_name: string, nis: ?string, parent_name: string, parent_phone: ?string, temp_password: ?string, created: bool}>
     */
    public function bulkProvisionMissingParents(?int $classroomId = null): array
    {
        $students = Student::with('user')
            ->whereNull('parent_id')
            ->when($classroomId, fn ($q) => $q->where('classroom_id', $classroomId))
            ->get();

        $results = [];

        DB::transaction(function () use ($students, &$results) {
            foreach ($students as $student) {
                $registration = Registration::where('user_id', $student->user_id)
                    ->orWhere(fn ($q) => $student->nisn ? $q->where('nisn', $student->nisn) : $q->whereRaw('1 = 0'))
                    ->latest('id')
                    ->first();

                if (! $registration || blank($registration->parent_phone)) {
                    continue;
                }

                $provisioned = $this->ensureParentForStudent(
                    $student,
                    $registration->parent_name,
                    $registration->parent_phone
                );

                if ($provisioned) {
                    $results[] = [
                        'student_name' => $student->user?->name ?? '-',
                        'nis' => $student->nis,
                        'parent_name' => $provisioned['user']->name,
                        'parent_phone' => $provisioned['user']->phone_number,
                        'temp_password' => $provisioned['plain_password'],
                        'created' => $provisioned['created'],
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Authorization matrix for password resets (`ALUR_KERJA.md` K5):
     * - TU / Admin / Superadmin: may reset non-superadmin accounts (Superadmin can reset anyone else).
     * - Homeroom teacher: may reset student accounts in their own homeroom class.
     */
    public function canResetPasswordFor(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        if ($target->role === 'superadmin' && $actor->role !== 'superadmin') {
            return false;
        }

        $roles = $actor->effectiveRoles();
        if ($actor->role === 'superadmin' || array_intersect($roles, ['admin', 'staf_tu']) !== []) {
            return true;
        }

        if (array_intersect($roles, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas']) === []) {
            return false;
        }

        return $this->isHomeroomStudentOf($actor, $target);
    }

    public function canViewClassroomCredentialSheet(User $actor, Classroom $classroom): bool
    {
        $roles = $actor->effectiveRoles();
        if ($actor->role === 'superadmin' || array_intersect($roles, ['admin', 'staf_tu']) !== []) {
            return true;
        }

        $year = AcademicYear::current();
        if ((int) $classroom->teacher_id !== $actor->id) {
            return false;
        }

        return $year === null || $classroom->academic_year === null || $classroom->academic_year === $year->year;
    }

    /**
     * Issue a new temporary password for `$target` and flag `must_change_password = true`.
     *
     * @return array{user: User, temp_password: string}
     */
    public function resetPasswordByActor(User $actor, User $target): array
    {
        if ($actor->id === $target->id) {
            throw new \DomainException(__('Gunakan menu Ubah Kata Sandi atau OTP WhatsApp untuk mengubah sandi Anda sendiri.'));
        }

        if (! $this->canResetPasswordFor($actor, $target)) {
            throw new AuthorizationException(__('Anda tidak berhak mereset kata sandi akun ini.'));
        }

        $tempPassword = $this->generateTemporaryPassword();

        DB::transaction(function () use ($actor, $target, $tempPassword) {
            $target->forceFill([
                'password' => Hash::make($tempPassword),
                'must_change_password' => true,
            ])->save();

            $target->tokens()->delete();

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'password_reset_by_operator',
                'auditable_type' => User::class,
                'auditable_id' => $target->id,
                'old_values' => ['must_change_password' => false],
                'new_values' => ['must_change_password' => true, 'reset_by_role' => $actor->role],
            ]);
        });

        return [
            'user' => $target->fresh(),
            'temp_password' => $tempPassword,
        ];
    }

    /**
     * Generate a printable credential sheet for all students & parents in a classroom.
     * Already-activated accounts (`must_change_password = false`) retain their own passwords
     * unless `$resetUnactivatedOnly === false`.
     *
     * @return array{classroom: Classroom, school_name: string, generated_at: string, reset_count: int, rows: list<array<string, mixed>>}
     */
    public function issueClassroomCredentialSheet(User $actor, Classroom $classroom, bool $resetUnactivatedOnly = true): array
    {
        if (! $this->canViewClassroomCredentialSheet($actor, $classroom)) {
            throw new AuthorizationException(__('Anda hanya dapat mencetak lembar akun untuk rombel perwalian Anda.'));
        }

        $students = Student::with(['user', 'parent'])
            ->where('classroom_id', $classroom->id)
            ->orderBy('nis')
            ->get();

        $rows = [];
        $resetCount = 0;
        $processedParentPasswords = [];

        DB::transaction(function () use ($actor, $classroom, $students, $resetUnactivatedOnly, &$rows, &$resetCount, &$processedParentPasswords) {
            foreach ($students as $student) {
                $studentUser = $student->user;
                $studentTempPassword = null;
                $studentActivated = false;

                if ($studentUser) {
                    if ($studentUser->must_change_password || ! $resetUnactivatedOnly) {
                        $studentTempPassword = $this->generateTemporaryPassword();
                        $studentUser->forceFill([
                            'password' => Hash::make($studentTempPassword),
                            'must_change_password' => true,
                        ])->save();
                        $resetCount++;
                    } else {
                        $studentActivated = true;
                    }
                }

                $parentUser = $student->parent;
                $parentTempPassword = null;
                $parentActivated = false;

                if ($parentUser) {
                    if (array_key_exists($parentUser->id, $processedParentPasswords)) {
                        $parentTempPassword = $processedParentPasswords[$parentUser->id];
                    } elseif ($parentUser->must_change_password) {
                        $parentTempPassword = $this->generateTemporaryPassword();
                        $parentUser->forceFill([
                            'password' => Hash::make($parentTempPassword),
                            'must_change_password' => true,
                        ])->save();
                        $processedParentPasswords[$parentUser->id] = $parentTempPassword;
                        $resetCount++;
                    } else {
                        $parentActivated = true;
                    }
                }

                $rows[] = [
                    'student_name' => $studentUser?->name ?? '-',
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'student_login' => $student->nisn ?: ($student->nis ?: ($studentUser?->email ?? '-')),
                    'student_email' => $studentUser?->email,
                    'student_temp_password' => $studentTempPassword,
                    'student_activated' => $studentActivated,
                    'parent_name' => $parentUser?->name,
                    'parent_login' => $parentUser?->phone_number ?: $parentUser?->email,
                    'parent_temp_password' => $parentTempPassword,
                    'parent_activated' => $parentActivated,
                ];
            }

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'classroom_credential_sheet_issued',
                'auditable_type' => Classroom::class,
                'auditable_id' => $classroom->id,
                'new_values' => [
                    'student_count' => count($rows),
                    'reset_count' => $resetCount,
                ],
            ]);
        });

        return [
            'classroom' => $classroom->load('teacher'),
            'school_name' => function_exists('school_name') ? school_name() : config('app.name'),
            'generated_at' => now()->format('d/m/Y H:i'),
            'reset_count' => $resetCount,
            'rows' => $rows,
        ];
    }

    public function buildClassroomCredentialPdf(array $sheetData)
    {
        return Pdf::loadView('users.credential-sheet-pdf', $sheetData)->setPaper('a4', 'landscape');
    }

    public function buildImportBatchCredentialPdf(array $credentials, string $batchLabel = 'Impor Dapodik')
    {
        return Pdf::loadView('users.import-credentials-pdf', [
            'credentials' => $credentials,
            'batch_label' => $batchLabel,
            'school_name' => function_exists('school_name') ? school_name() : config('app.name'),
            'generated_at' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');
    }

    private function isHomeroomStudentOf(User $teacher, User $target): bool
    {
        if (! in_array($target->role, StudentDataAccess::STUDENT_ROLES, true)) {
            return false;
        }

        $student = $target->student;
        if (! $student || ! $student->classroom_id) {
            return false;
        }

        $classroom = Classroom::find($student->classroom_id);
        if (! $classroom || (int) $classroom->teacher_id !== $teacher->id) {
            return false;
        }

        $year = AcademicYear::current();

        return $year === null || $classroom->academic_year === null || $classroom->academic_year === $year->year;
    }

    private function sanitizeEmail(?string $email): ?string
    {
        $clean = trim((string) $email);
        if ($clean === '' || ! filter_var($clean, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return strtolower($clean);
    }
}
