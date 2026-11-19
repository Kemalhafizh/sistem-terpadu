<?php

namespace App\Services\Auth;

use App\Models\Employee;
use App\Models\Student;
use App\Models\User;

class UserIdentifierResolver
{
    /**
     * Resolve a User account from email, phone number (08 / 62 / +62),
     * student NIS / NISN, or employee NIY/NIP / NUPTK.
     */
    public function findByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        // 1. Direct or case-insensitive email match.
        if (str_contains($identifier, '@')) {
            $user = User::where('email', $identifier)
                ->orWhereRaw('LOWER(email) = ?', [mb_strtolower($identifier)])
                ->first();
            if ($user) {
                return $user;
            }
        }

        // 2. Indonesian phone number variants (08xxx / 628xxx / +628xxx).
        $phoneVariants = $this->phoneVariants($identifier);
        if ($phoneVariants !== []) {
            $user = User::whereIn('phone_number', $phoneVariants)->first();
            if ($user) {
                return $user;
            }
        }

        // 3. Student NIS or NISN.
        $student = Student::with('user')
            ->where('nis', $identifier)
            ->orWhere('nisn', $identifier)
            ->first();
        if ($student?->user) {
            return $student->user;
        }

        // 4. Employee NIP/NIY (`employee_number`) or NUPTK (`nuptk`).
        $employee = Employee::with('user')
            ->where('employee_number', $identifier)
            ->orWhere('nuptk', $identifier)
            ->first();
        if ($employee?->user) {
            return $employee->user;
        }

        return null;
    }

    /**
     * Normalize Indonesian phone input to `08...` canonical format when possible.
     */
    public function normalizeLocalPhone(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', trim($raw)) ?? '';
        if (strlen($digits) < 8) {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0'.$digits;
        }

        return $digits;
    }

    /**
     * Produce all plausible stored phone strings for lookups.
     *
     * @return list<string>
     */
    public function phoneVariants(?string $raw): array
    {
        if ($raw === null) {
            return [];
        }

        $digits = preg_replace('/\D+/', '', trim($raw)) ?? '';
        if (strlen($digits) < 8) {
            return [];
        }

        $variants = [trim($raw), $digits];

        if (str_starts_with($digits, '0')) {
            $intl = '62'.substr($digits, 1);
            $variants[] = $digits;
            $variants[] = $intl;
            $variants[] = '+'.$intl;
        } elseif (str_starts_with($digits, '62')) {
            $local = '0'.substr($digits, 2);
            $variants[] = $local;
            $variants[] = $digits;
            $variants[] = '+'.$digits;
        } elseif (str_starts_with($digits, '8')) {
            $variants[] = '0'.$digits;
            $variants[] = '62'.$digits;
            $variants[] = '+62'.$digits;
        }

        return array_values(array_unique(array_filter($variants)));
    }
}
