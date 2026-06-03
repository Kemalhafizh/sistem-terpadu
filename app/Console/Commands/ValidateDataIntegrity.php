<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateDataIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek integritas dan konsistensi data di seluruh database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('🔍 MEMULAI PEMERIKSAAN INTEGRITAS DATABASE SISTEM TERPADU');
        $this->info('================================================================');

        $errors = [];
        $checks = [];

        // 1. Audit Table: Users
        $this->line('Pemeriksaan Tabel: users...');
        $userChecks = $this->auditUsers($errors);
        $checks = array_merge($checks, $userChecks);

        // 2. Audit Table: Students
        $this->line('Pemeriksaan Tabel: students...');
        $studentChecks = $this->auditStudents($errors);
        $checks = array_merge($checks, $studentChecks);

        // 3. Audit Table: Employees
        $this->line('Pemeriksaan Tabel: employees...');
        $employeeChecks = $this->auditEmployees($errors);
        $checks = array_merge($checks, $employeeChecks);

        // 4. Audit Table: Grades
        $this->line('Pemeriksaan Tabel: grades...');
        $gradeChecks = $this->auditGrades($errors);
        $checks = array_merge($checks, $gradeChecks);

        // 5. Audit Table: Attendances
        $this->line('Pemeriksaan Tabel: attendances...');
        $attendanceChecks = $this->auditAttendances($errors);
        $checks = array_merge($checks, $attendanceChecks);

        // 6. Audit Table: Billings
        $this->line('Pemeriksaan Tabel: billings...');
        $billingChecks = $this->auditBillings($errors);
        $checks = array_merge($checks, $billingChecks);

        $this->info("\n================================================================");
        $this->info('📊 RINGKASAN PEMERIKSAAN INTEGRITAS');
        $this->info('================================================================');

        // Render summary table
        $this->table(
            ['Modul/Tabel', 'Pemeriksaan', 'Status', 'Jumlah Kesalahan'],
            $checks
        );

        if (count($errors) > 0) {
            $this->error("\n❌ Ditemukan " . count($errors) . " isu integritas data di database!");
            $this->warn("\nDetail Kesalahan:");
            foreach ($errors as $idx => $err) {
                $this->line(($idx + 1) . '. [' . $err['table'] . '] ID/Value: ' . $err['id'] . ' - ' . $err['message']);
            }
            return Command::FAILURE;
        }

        $this->info("\n✅ Integritas data sempurna! Tidak ditemukan kesalahan konsistensi.");
        return Command::SUCCESS;
    }

    /**
     * Audit users table.
     */
    private function auditUsers(array &$errors): array
    {
        $checks = [];

        // Check 1: Duplicate Emails
        $duplicates = DB::table('users')
            ->select('email', DB::raw('count(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();
        $dupCount = $duplicates->count();
        $checks[] = ['users', 'Duplikasi Email', $dupCount == 0 ? 'OK' : 'FAIL', $dupCount];
        foreach ($duplicates as $d) {
            $errors[] = [
                'table' => 'users',
                'id' => $d->email,
                'message' => "Email terduplikasi sebanyak {$d->count} kali."
            ];
        }

        // Check 2: Invalid Email Formats
        $users = DB::table('users')->get();
        $invalidEmails = $users->filter(function ($user) {
            return !filter_var($user->email, FILTER_VALIDATE_EMAIL);
        });
        $invCount = $invalidEmails->count();
        $checks[] = ['users', 'Format Email Valid', $invCount == 0 ? 'OK' : 'FAIL', $invCount];
        foreach ($invalidEmails as $u) {
            $errors[] = [
                'table' => 'users',
                'id' => $u->id,
                'message' => "Format email tidak valid: '{$u->email}'"
            ];
        }

        // Check 3: Empty Fields
        $emptyFields = DB::table('users')
            ->whereNull('name')->orWhere('name', '')
            ->orWhereNull('email')->orWhere('email', '')
            ->orWhereNull('role')->orWhere('role', '')
            ->get();
        $emptyCount = $emptyFields->count();
        $checks[] = ['users', 'Kelengkapan Field Wajib', $emptyCount == 0 ? 'OK' : 'FAIL', $emptyCount];
        foreach ($emptyFields as $u) {
            $errors[] = [
                'table' => 'users',
                'id' => $u->id ?? 'N/A',
                'message' => "Nama, email, atau role kosong."
            ];
        }

        return $checks;
    }

    /**
     * Audit students table.
     */
    private function auditStudents(array &$errors): array
    {
        $checks = [];

        // Check 1: Orphaned User IDs
        $orphanedUsers = DB::table('students')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('users')->whereRaw('users.id = students.user_id');
            })->get();
        $orphanedUsersCount = $orphanedUsers->count();
        $checks[] = ['students', 'Orphaned User Relation', $orphanedUsersCount == 0 ? 'OK' : 'FAIL', $orphanedUsersCount];
        foreach ($orphanedUsers as $s) {
            $errors[] = [
                'table' => 'students',
                'id' => $s->id,
                'message' => "User ID '{$s->user_id}' tidak ditemukan di tabel users."
            ];
        }

        // Check 2: Orphaned Classroom IDs
        $orphanedClassrooms = DB::table('students')
            ->whereNotNull('classroom_id')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('classrooms')->whereRaw('classrooms.id = students.classroom_id');
            })->get();
        $orphanedClassroomsCount = $orphanedClassrooms->count();
        $checks[] = ['students', 'Orphaned Classroom Relation', $orphanedClassroomsCount == 0 ? 'OK' : 'FAIL', $orphanedClassroomsCount];
        foreach ($orphanedClassrooms as $s) {
            $errors[] = [
                'table' => 'students',
                'id' => $s->id,
                'message' => "Classroom ID '{$s->classroom_id}' tidak ditemukan di tabel classrooms."
            ];
        }

        // Check 3: Duplicate NIS
        $dupNis = DB::table('students')
            ->select('nis', DB::raw('count(*) as count'))
            ->whereNotNull('nis')
            ->groupBy('nis')
            ->having('count', '>', 1)
            ->get();
        $dupNisCount = $dupNis->count();
        $checks[] = ['students', 'Duplikasi NIS', $dupNisCount == 0 ? 'OK' : 'FAIL', $dupNisCount];
        foreach ($dupNis as $d) {
            $errors[] = [
                'table' => 'students',
                'id' => $d->nis,
                'message' => "NIS terduplikasi sebanyak {$d->count} kali."
            ];
        }

        return $checks;
    }

    /**
     * Audit employees table.
     */
    private function auditEmployees(array &$errors): array
    {
        $checks = [];

        // Check 1: Orphaned User IDs
        $orphanedUsers = DB::table('employees')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('users')->whereRaw('users.id = employees.user_id');
            })->get();
        $orphanedUsersCount = $orphanedUsers->count();
        $checks[] = ['employees', 'Orphaned User Relation', $orphanedUsersCount == 0 ? 'OK' : 'FAIL', $orphanedUsersCount];
        foreach ($orphanedUsers as $e) {
            $errors[] = [
                'table' => 'employees',
                'id' => $e->id,
                'message' => "User ID '{$e->user_id}' tidak ditemukan di tabel users."
            ];
        }

        // Check 2: Duplicate Employee Number
        $dupNip = DB::table('employees')
            ->select('employee_number', DB::raw('count(*) as count'))
            ->whereNotNull('employee_number')
            ->groupBy('employee_number')
            ->having('count', '>', 1)
            ->get();
        $dupNipCount = $dupNip->count();
        $checks[] = ['employees', 'Duplikasi NIP/NIY', $dupNipCount == 0 ? 'OK' : 'FAIL', $dupNipCount];
        foreach ($dupNip as $d) {
            $errors[] = [
                'table' => 'employees',
                'id' => $d->employee_number,
                'message' => "Nomor Pegawai terduplikasi sebanyak {$d->count} kali."
            ];
        }

        return $checks;
    }

    /**
     * Audit grades table.
     */
    private function auditGrades(array &$errors): array
    {
        $checks = [];

        // Check 1: Score Boundaries
        $boundaryGrades = DB::table('grades')->where('score', '<', 0)->orWhere('score', '>', 100)->get();
        $boundCount = $boundaryGrades->count();
        $checks[] = ['grades', 'Batas Nilai (0-100)', $boundCount == 0 ? 'OK' : 'FAIL', $boundCount];
        foreach ($boundaryGrades as $g) {
            $errors[] = [
                'table' => 'grades',
                'id' => $g->id,
                'message' => "Nilai '{$g->score}' berada di luar batas valid 0-100."
            ];
        }

        // Check 2: Orphaned Student IDs
        $orphanedStudents = DB::table('grades')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('students')->whereRaw('students.id = grades.student_id');
            })->get();
        $orphCount = $orphanedStudents->count();
        $checks[] = ['grades', 'Orphaned Student Relation', $orphCount == 0 ? 'OK' : 'FAIL', $orphCount];
        foreach ($orphanedStudents as $g) {
            $errors[] = [
                'table' => 'grades',
                'id' => $g->id,
                'message' => "Student ID '{$g->student_id}' tidak ditemukan di tabel students."
            ];
        }

        return $checks;
    }

    /**
     * Audit attendances table.
     */
    private function auditAttendances(array &$errors): array
    {
        $checks = [];

        // Check 1: Invalid Status Codes
        $invalidStatus = DB::table('attendances')->whereNotIn('status', ['H', 'I', 'S', 'A'])->get();
        $invCount = $invalidStatus->count();
        $checks[] = ['attendances', 'Validitas Kode Presensi (H,I,S,A)', $invCount == 0 ? 'OK' : 'FAIL', $invCount];
        foreach ($invalidStatus as $a) {
            $errors[] = [
                'table' => 'attendances',
                'id' => $a->id,
                'message' => "Kode kehadiran tidak valid: '{$a->status}'."
            ];
        }

        // Check 2: Future Dates
        $futureDates = DB::table('attendances')->where('date', '>', now()->toDateString())->get();
        $futureCount = $futureDates->count();
        $checks[] = ['attendances', 'Batasan Tanggal Masa Depan', $futureCount == 0 ? 'OK' : 'FAIL', $futureCount];
        foreach ($futureDates as $a) {
            $errors[] = [
                'table' => 'attendances',
                'id' => $a->id,
                'message' => "Pencatatan tanggal absensi di masa depan: '{$a->date}'."
            ];
        }

        return $checks;
    }

    /**
     * Audit billings table.
     */
    private function auditBillings(array &$errors): array
    {
        $checks = [];

        // Check 1: Negative amounts/balances
        $negativeBillings = DB::table('billings')->where('amount', '<', 0)->orWhere('remaining_balance', '<', 0)->get();
        $negCount = $negativeBillings->count();
        $checks[] = ['billings', 'Batasan Nilai Tagihan Positif', $negCount == 0 ? 'OK' : 'FAIL', $negCount];
        foreach ($negativeBillings as $b) {
            $errors[] = [
                'table' => 'billings',
                'id' => $b->id,
                'message' => "Nilai tagihan/sisa saldo negatif (Amount: {$b->amount}, Remaining: {$b->remaining_balance})."
            ];
        }

        // Check 2: Orphaned Student IDs
        $orphanedStudents = DB::table('billings')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('students')->whereRaw('students.id = billings.student_id');
            })->get();
        $orphCount = $orphanedStudents->count();
        $checks[] = ['billings', 'Orphaned Student Relation', $orphCount == 0 ? 'OK' : 'FAIL', $orphCount];
        foreach ($orphanedStudents as $b) {
            $errors[] = [
                'table' => 'billings',
                'id' => $b->id,
                'message' => "Student ID '{$b->student_id}' tidak ditemukan di tabel students."
            ];
        }

        return $checks;
    }
}
