<?php

namespace App\Services\Integration;

use App\Models\Student;
use App\Models\User;
use App\Models\SyncConfig;
use App\Models\SyncReport;
use Carbon\Carbon;

class SyncMapperService
{
    /**
     * Get default mapping array.
     */
    public function getDefaultMapping(string $target, string $table): array
    {
        if ($target === 'dapodik') {
            if ($table === 'students') {
                return [
                    'nis' => 'nomor_induk',
                    'nisn' => 'nisn',
                    'name' => 'nama_lengkap',
                    'gender' => 'jenis_kelamin',
                    'birth_date' => 'tanggal_lahir',
                    'address' => 'alamat',
                    'classroom' => 'rombongan_belajar',
                ];
            } elseif ($table === 'teachers') {
                return [
                    'name' => 'nama_ptk',
                    'email' => 'email_aktif',
                    'nip' => 'nip',
                    'status' => 'status_kepegawaian',
                ];
            }
        } elseif ($target === 'emis') {
            if ($table === 'students') {
                return [
                    'nis' => 'nomor_statistik',
                    'nisn' => 'nisn',
                    'name' => 'nama',
                    'gender' => 'jenis_kelamin',
                    'birth_date' => 'tanggal_lahir',
                    'address' => 'alamat_lengkap',
                    'classroom' => 'kelas',
                ];
            } elseif ($table === 'teachers') {
                return [
                    'name' => 'nama_lengkap',
                    'email' => 'surel',
                    'nip' => 'nip_pegawai',
                    'status' => 'status',
                ];
            }
        }

        return [];
    }

    /**
     * Run data audit validation against kementerian rules.
     */
    public function validateData(string $target, string $table): array
    {
        $issues = [];

        if ($table === 'students') {
            $students = Student::with(['user', 'classroom'])->get();

            foreach ($students as $student) {
                $studentName = $student->user->name ?? 'Siswa Tanpa Nama';

                // Rule 1: NISN must be 10 digits
                if (empty($student->nisn)) {
                    $issues[] = [
                        'id' => $student->id,
                        'name' => $studentName,
                        'identifier' => 'NIS: ' . ($student->nis ?: '-'),
                        'field' => 'nisn',
                        'issue' => 'NISN kosong.',
                        'severity' => 'error',
                    ];
                } elseif (! preg_match('/^[0-9]{10}$/', $student->nisn)) {
                    $issues[] = [
                        'id' => $student->id,
                        'name' => $studentName,
                        'identifier' => 'NIS: ' . ($student->nis ?: '-'),
                        'field' => 'nisn',
                        'issue' => 'NISN tidak valid (harus 10 digit angka).',
                        'severity' => 'warning',
                    ];
                }

                // Rule 2: Gender must be L/P or male/female correctly mapped
                if (empty($student->gender)) {
                    $issues[] = [
                        'id' => $student->id,
                        'name' => $studentName,
                        'identifier' => 'NIS: ' . ($student->nis ?: '-'),
                        'field' => 'gender',
                        'issue' => 'Jenis kelamin kosong.',
                        'severity' => 'error',
                    ];
                }

                // Rule 3: Birth date in past
                if (empty($student->birth_date)) {
                    $issues[] = [
                        'id' => $student->id,
                        'name' => $studentName,
                        'identifier' => 'NIS: ' . ($student->nis ?: '-'),
                        'field' => 'birth_date',
                        'issue' => 'Tanggal lahir kosong.',
                        'severity' => 'error',
                    ];
                } elseif (Carbon::parse($student->birth_date)->isFuture()) {
                    $issues[] = [
                        'id' => $student->id,
                        'name' => $studentName,
                        'identifier' => 'NIS: ' . ($student->nis ?: '-'),
                        'field' => 'birth_date',
                        'issue' => 'Tanggal lahir tidak valid (di masa depan).',
                        'severity' => 'error',
                    ];
                }

                // Rule 4: Classroom must not be empty
                if (empty($student->classroom_id)) {
                    $issues[] = [
                        'id' => $student->id,
                        'name' => $studentName,
                        'identifier' => 'NIS: ' . ($student->nis ?: '-'),
                        'field' => 'classroom',
                        'issue' => 'Siswa tidak terdaftar di rombongan belajar (kelas) mana pun.',
                        'severity' => 'warning',
                    ];
                }
            }
        } elseif ($table === 'teachers') {
            $teachers = User::whereIn('role', ['guru', 'bk'])->with('employee')->get();

            foreach ($teachers as $teacher) {
                // Rule 1: NIP for PNS must not be empty
                $emp = $teacher->employee;
                if ($emp) {
                    if ($emp->status === 'pns' && empty($emp->nip)) {
                        $issues[] = [
                            'id' => $teacher->id,
                            'name' => $teacher->name,
                            'identifier' => 'NIP: -',
                            'field' => 'nip',
                            'issue' => 'Status PNS tetapi NIP kosong.',
                            'severity' => 'error',
                        ];
                    }
                } else {
                    $issues[] = [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'identifier' => 'NIP: -',
                        'field' => 'employee_record',
                        'issue' => 'Profil pegawai (Employee record) belum dibuat.',
                        'severity' => 'warning',
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * Map database records to kementerian target columns schema.
     */
    public function mapData(string $target, string $table, array $mapping): array
    {
        $mappedData = [];

        if ($table === 'students') {
            $students = Student::with(['user', 'classroom'])->get();

            foreach ($students as $student) {
                $row = [];
                foreach ($mapping as $localKey => $targetKey) {
                    switch ($localKey) {
                        case 'nis':
                            $row[$targetKey] = $student->nis;
                            break;
                        case 'nisn':
                            $row[$targetKey] = $student->nisn;
                            break;
                        case 'name':
                            $row[$targetKey] = $student->user->name ?? '';
                            break;
                        case 'gender':
                            $gender = $student->gender;
                            if ($gender === 'male' || $gender === 'L') {
                                $row[$targetKey] = 'L';
                            } elseif ($gender === 'female' || $gender === 'P') {
                                $row[$targetKey] = 'P';
                            } else {
                                $row[$targetKey] = $gender;
                            }
                            break;
                        case 'birth_date':
                            $row[$targetKey] = $student->birth_date;
                            break;
                        case 'address':
                            $row[$targetKey] = $student->address;
                            break;
                        case 'classroom':
                            $row[$targetKey] = $student->classroom->name ?? '';
                            break;
                    }
                }
                $mappedData[] = $row;
            }
        } elseif ($table === 'teachers') {
            $teachers = User::whereIn('role', ['guru', 'bk'])->with('employee')->get();

            foreach ($teachers as $teacher) {
                $row = [];
                foreach ($mapping as $localKey => $targetKey) {
                    switch ($localKey) {
                        case 'name':
                            $row[$targetKey] = $teacher->name;
                            break;
                        case 'email':
                            $row[$targetKey] = $teacher->email;
                            break;
                        case 'nip':
                            $row[$targetKey] = $teacher->employee->nip ?? '';
                            break;
                        case 'status':
                            $row[$targetKey] = $teacher->employee->status ?? '';
                            break;
                    }
                }
                $mappedData[] = $row;
            }
        }

        return $mappedData;
    }

    /**
     * Save synchronization run report.
     */
    public function saveReport(string $target, string $table, int $processed, int $success, int $failed, array $errorLogs, int $runBy): SyncReport
    {
        return SyncReport::create([
            'system_target' => $target,
            'local_table' => $table,
            'sync_run_at' => now(),
            'records_processed' => $processed,
            'records_success' => $success,
            'records_failed' => $failed,
            'error_logs' => $errorLogs,
            'run_by' => $runBy,
        ]);
    }
}
