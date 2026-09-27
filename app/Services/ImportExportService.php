<?php

namespace App\Services;

use App\Exports\GenericExport;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\ImportLog;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportService
{
    /**
     * Get list of modules supported by the Import/Export Hub.
     */
    public function getAvailableModules(): array
    {
        return [
            'students' => 'Data Siswa (Students)',
            'employees' => 'Data Pegawai (Employees)',
            'subjects' => 'Mata Pelajaran (Subjects)',
            'classrooms' => 'Daftar Kelas (Classrooms)',
        ];
    }

    /**
     * Get column configuration and validation rules for a specific module.
     */
    public function getModuleColumns(string $module): array
    {
        switch ($module) {
            case 'students':
                return [
                    'name' => [
                        'label' => 'Nama Lengkap (User)',
                        'required' => true,
                        'rules' => 'required|string|max:255',
                        'description' => 'Nama lengkap siswa untuk profil pengguna.',
                    ],
                    'email' => [
                        'label' => 'Email (User)',
                        'required' => true,
                        'rules' => 'required|email|unique:users,email',
                        'description' => 'Alamat email unik untuk kredensial login.',
                    ],
                    'nis' => [
                        'label' => 'NIS (Nomor Induk Siswa)',
                        'required' => true,
                        'rules' => 'required|string|unique:students,nis',
                        'description' => 'Nomor Induk Siswa (juga dijadikan password awal).',
                    ],
                    'nisn' => [
                        'label' => 'NISN',
                        'required' => true,
                        'rules' => 'required|string|unique:students,nisn',
                        'description' => 'Nomor Induk Siswa Nasional.',
                    ],
                    'gender' => [
                        'label' => 'Jenis Kelamin (L/P)',
                        'required' => true,
                        'rules' => 'required|in:L,P,l,p',
                        'description' => 'L untuk Laki-laki, P untuk Perempuan.',
                    ],
                    'classroom' => [
                        'label' => 'Nama / ID Kelas',
                        'required' => false,
                        'rules' => 'nullable|string',
                        'description' => 'Dapat berupa Nama Kelas (contoh: X-A) atau ID Kelas numerik.',
                    ],
                    'birth_date' => [
                        'label' => 'Tanggal Lahir (YYYY-MM-DD)',
                        'required' => false,
                        'rules' => 'nullable|date_format:Y-m-d',
                        'description' => 'Format tanggal: Tahun-Bulan-Hari (contoh: 2010-08-15).',
                    ],
                    'address' => [
                        'label' => 'Alamat Rumah',
                        'required' => false,
                        'rules' => 'nullable|string',
                        'description' => 'Alamat lengkap tempat tinggal siswa.',
                    ],
                ];

            case 'employees':
                return [
                    'name' => [
                        'label' => 'Nama Lengkap (User)',
                        'required' => true,
                        'rules' => 'required|string|max:255',
                        'description' => 'Nama lengkap pegawai untuk profil pengguna.',
                    ],
                    'email' => [
                        'label' => 'Email (User)',
                        'required' => true,
                        'rules' => 'required|email|unique:users,email',
                        'description' => 'Alamat email unik untuk kredensial login.',
                    ],
                    'phone_number' => [
                        'label' => 'Nomor Telepon',
                        'required' => false,
                        'rules' => 'nullable|string',
                        'description' => 'Nomor WhatsApp / handphone aktif.',
                    ],
                    'role' => [
                        'label' => 'Role (guru/staf_tu/bk/dll)',
                        'required' => true,
                        'rules' => 'required|in:guru,staf_tu,bk,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas',
                        'description' => 'Role hak akses di sistem.',
                    ],
                    'employee_number' => [
                        'label' => 'NIY / NIP',
                        'required' => false,
                        'rules' => 'nullable|string|unique:employees,employee_number',
                        'description' => 'Nomor Induk Yayasan atau Pegawai (juga password login).',
                    ],
                    'position' => [
                        'label' => 'Jabatan Detail',
                        'required' => true,
                        'rules' => 'required|string|max:255',
                        'description' => 'Contoh: Guru Fisika, Kepala Lab, Staf Administrasi.',
                    ],
                    'employment_status' => [
                        'label' => 'Status Kerja (gty/gtt/staf_tetap/honorer)',
                        'required' => true,
                        'rules' => 'required|in:gty,gtt,staf_tetap,honorer',
                        'description' => 'gty = Guru Tetap, gtt = Guru Tidak Tetap, staf_tetap, honorer.',
                    ],
                    'base_salary' => [
                        'label' => 'Gaji Pokok (Angka)',
                        'required' => false,
                        'rules' => 'nullable|numeric|min:0',
                        'description' => 'Gaji pokok bulanan (contoh: 3500000).',
                    ],
                    'bank_name' => [
                        'label' => 'Nama Bank',
                        'required' => false,
                        'rules' => 'nullable|string',
                        'description' => 'Nama bank untuk payroll (contoh: BNI, Mandiri).',
                    ],
                    'bank_account' => [
                        'label' => 'Nomor Rekening',
                        'required' => false,
                        'rules' => 'nullable|string',
                        'description' => 'Nomor rekening bank penerima.',
                    ],
                ];

            case 'subjects':
                return [
                    'code' => [
                        'label' => 'Kode Mapel',
                        'required' => true,
                        'rules' => 'required|string|unique:subjects,code',
                        'description' => 'Kode unik mata pelajaran (contoh: IPA-10).',
                    ],
                    'name' => [
                        'label' => 'Nama Mata Pelajaran',
                        'required' => true,
                        'rules' => 'required|string|max:255',
                        'description' => 'Nama mata pelajaran (contoh: Ilmu Pengetahuan Alam).',
                    ],
                    'grade_level' => [
                        'label' => 'Tingkat Kelas (X/XI/XII)',
                        'required' => true,
                        'rules' => 'required|in:X,XI,XII',
                        'description' => 'Tingkat kelas sasaran.',
                    ],
                    'kkm' => [
                        'label' => 'KKM (Kriteria Ketuntasan)',
                        'required' => true,
                        'rules' => 'required|integer|min:0|max:100',
                        'description' => 'Nilai KKM minimal (contoh: 75).',
                    ],
                ];

            case 'classrooms':
                return [
                    'name' => [
                        'label' => 'Nama Kelas',
                        'required' => true,
                        'rules' => 'required|string|max:255|unique:classrooms,name',
                        'description' => 'Nama ruang kelas unik (contoh: X IPA 1).',
                    ],
                    'grade_level' => [
                        'label' => 'Tingkat Kelas (X/XI/XII)',
                        'required' => true,
                        'rules' => 'required|in:X,XI,XII',
                        'description' => 'Tingkat kelas.',
                    ],
                    'major' => [
                        'label' => 'Jurusan (IPA/IPS/Bahasa/Umum)',
                        'required' => true,
                        'rules' => 'required|string|max:50',
                        'description' => 'Jurusan kelas (contoh: IPA, IPS, atau Umum).',
                    ],
                    'academic_year' => [
                        'label' => 'Tahun Akademik',
                        'required' => true,
                        'rules' => 'required|string',
                        'description' => 'Tahun akademik kelas (contoh: 2025/2026).',
                    ],
                ];

            default:
                return [];
        }
    }

    /**
     * Parse uploaded Excel or CSV file to extract headers and preview rows.
     */
    public function parseUploadedFile(UploadedFile $file): array
    {
        $sheets = Excel::toArray(new class {}, $file);
        $rows = $sheets[0] ?? [];

        if (empty($rows)) {
            return [
                'headers' => [],
                'preview' => [],
                'all_data' => [],
            ];
        }

        // Clean headers: remove trailing/leading spaces
        $headers = array_map(function ($val) {
            return $val !== null ? trim((string) $val) : '';
        }, $rows[0]);

        $dataRows = array_slice($rows, 1);

        // Remove completely empty rows from all_data
        $dataRows = array_filter($dataRows, function ($row) {
            return ! empty(array_filter($row, fn ($v) => $v !== null && $v !== ''));
        });

        // Take up to 10 rows for preview
        $preview = array_slice($dataRows, 0, 10);

        return [
            'headers' => $headers,
            'preview' => $preview,
            'all_data' => array_values($dataRows),
        ];
    }

    /**
     * Validate data rows against dynamic column mapping and module rules.
     */
    public function validateMapping(string $module, array $mapping, array $data): array
    {
        $config = $this->getModuleColumns($module);
        $errors = [];

        foreach ($data as $index => $row) {
            $mappedData = [];
            foreach ($mapping as $dbField => $fileColIndex) {
                if ($fileColIndex !== '' && isset($row[$fileColIndex])) {
                    $mappedData[$dbField] = $row[$fileColIndex];
                } else {
                    $mappedData[$dbField] = null;
                }
            }

            // Custom checks before validator
            if ($module === 'students' && ! empty($mappedData['gender'])) {
                $mappedData['gender'] = strtoupper(trim($mappedData['gender']));
            }
            if ($module === 'students' && isset($mappedData['nis'])) {
                $mappedData['nis'] = (string) $mappedData['nis'];
            }
            if ($module === 'students' && isset($mappedData['nisn'])) {
                $mappedData['nisn'] = (string) $mappedData['nisn'];
            }
            if ($module === 'employees' && ! empty($mappedData['employment_status'])) {
                $mappedData['employment_status'] = strtolower(trim($mappedData['employment_status']));
            }
            if ($module === 'employees' && ! empty($mappedData['role'])) {
                $mappedData['role'] = strtolower(trim($mappedData['role']));
            }
            if ($module === 'employees' && isset($mappedData['employee_number'])) {
                $mappedData['employee_number'] = (string) $mappedData['employee_number'];
            }

            $rules = [];
            foreach ($config as $field => $fieldConfig) {
                $rules[$field] = $fieldConfig['rules'];
            }

            // Exclude unique checks for email/NIS if we are just preview-validating (since it checks active DB, which is correct)
            $validator = Validator::make($mappedData, $rules);

            if ($validator->fails()) {
                $errors[$index + 2] = $validator->errors()->all(); // Row 2 is first data row (1-indexed, after header)
            }
        }

        return $errors;
    }

    /**
     * Execute batch import within a secure database transaction, keeping logs of created IDs for rollback.
     */
    public function executeImport(string $module, array $mapping, array $data, string $fileName, int $userId): array
    {
        $config = $this->getModuleColumns($module);
        $createdIds = [
            'users' => [],
            'students' => [],
            'employees' => [],
            'subjects' => [],
            'classrooms' => [],
        ];

        $successCount = 0;
        $errorCount = 0;
        $errorDetails = [];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                $mappedData = [];
                foreach ($mapping as $dbField => $fileColIndex) {
                    if ($fileColIndex !== '' && isset($row[$fileColIndex])) {
                        $mappedData[$dbField] = $row[$fileColIndex];
                    } else {
                        $mappedData[$dbField] = null;
                    }
                }

                // Clean data values
                if (isset($mappedData['gender'])) {
                    $mappedData['gender'] = strtoupper(trim((string) $mappedData['gender']));
                }
                if (isset($mappedData['nis'])) {
                    $mappedData['nis'] = (string) $mappedData['nis'];
                }
                if (isset($mappedData['nisn'])) {
                    $mappedData['nisn'] = (string) $mappedData['nisn'];
                }
                if (isset($mappedData['employment_status'])) {
                    $mappedData['employment_status'] = strtolower(trim((string) $mappedData['employment_status']));
                }
                if (isset($mappedData['role'])) {
                    $mappedData['role'] = strtolower(trim((string) $mappedData['role']));
                }
                if (isset($mappedData['employee_number'])) {
                    $mappedData['employee_number'] = (string) $mappedData['employee_number'];
                }

                // Row-level validation
                $rules = [];
                foreach ($config as $field => $fieldConfig) {
                    $rules[$field] = $fieldConfig['rules'];
                }

                $validator = Validator::make($mappedData, $rules);
                if ($validator->fails()) {
                    $errorCount++;
                    $errorDetails[$index + 2] = $validator->errors()->all();

                    continue;
                }

                // Insert into Database
                if ($module === 'students') {
                    // 1. Create User
                    $user = User::create([
                        'name' => $mappedData['name'],
                        'email' => $mappedData['email'],
                        'password' => Hash::make($mappedData['nis']), // Use NIS as initial password
                        'role' => 'student',
                        'must_change_password' => true,
                    ]);
                    $createdIds['users'][] = $user->id;

                    // Search Classroom by Name or ID
                    $classroomId = null;
                    $classroomVal = $mappedData['classroom'] ?? null;
                    if (! empty($classroomVal)) {
                        if (is_numeric($classroomVal)) {
                            $classroom = Classroom::find($classroomVal);
                        } else {
                            $classroom = Classroom::where('name', 'like', "%$classroomVal%")->first();
                        }
                        $classroomId = $classroom ? $classroom->id : null;
                    }

                    // 2. Create Student
                    $student = Student::create([
                        'user_id' => $user->id,
                        'classroom_id' => $classroomId,
                        'nis' => $mappedData['nis'],
                        'nisn' => $mappedData['nisn'],
                        'gender' => $mappedData['gender'],
                        'birth_date' => $mappedData['birth_date'] ?? null,
                        'address' => $mappedData['address'] ?? null,
                        'status' => 'active',
                    ]);
                    $createdIds['students'][] = $student->id;

                } elseif ($module === 'employees') {
                    // 1. Create User
                    $empNum = ($mappedData['employee_number'] ?? null) ?: rand(100000, 999999);
                    $user = User::create([
                        'name' => $mappedData['name'],
                        'email' => $mappedData['email'],
                        'phone_number' => $mappedData['phone_number'] ?? null,
                        'password' => Hash::make($empNum),
                        'role' => $mappedData['role'],
                        'must_change_password' => true,
                    ]);
                    $createdIds['users'][] = $user->id;

                    // 2. Create Employee
                    $employee = Employee::create([
                        'user_id' => $user->id,
                        'employee_number' => $mappedData['employee_number'] ?? null,
                        'position' => $mappedData['position'],
                        'employment_status' => $mappedData['employment_status'],
                        'base_salary' => $mappedData['base_salary'] ?? 0,
                        'bank_name' => $mappedData['bank_name'] ?? null,
                        'bank_account' => $mappedData['bank_account'] ?? null,
                    ]);
                    $createdIds['employees'][] = $employee->id;

                } elseif ($module === 'subjects') {
                    // Create Subject
                    $subject = Subject::create([
                        'code' => $mappedData['code'],
                        'name' => $mappedData['name'],
                        'grade_level' => $mappedData['grade_level'],
                        'kkm' => $mappedData['kkm'],
                    ]);
                    $createdIds['subjects'][] = $subject->id;

                } elseif ($module === 'classrooms') {
                    // Create Classroom
                    $classroom = Classroom::create([
                        'name' => $mappedData['name'],
                        'grade_level' => $mappedData['grade_level'],
                        'major' => $mappedData['major'],
                        'academic_year' => $mappedData['academic_year'],
                    ]);
                    $createdIds['classrooms'][] = $classroom->id;
                }

                $successCount++;
            }

            // Save Import Log
            $log = ImportLog::create([
                'module' => $module,
                'imported_by' => $userId,
                'file_name' => $fileName,
                'total_rows' => count($data),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'error_details' => $errorDetails,
                'created_ids' => $createdIds,
                'status' => $successCount > 0 ? 'completed' : 'failed',
            ]);

            DB::commit();

            return [
                'success' => true,
                'log_id' => $log->id,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errorDetails,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal mengimpor batch data: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Rollback a processed batch import completely, purging created database records.
     */
    public function rollbackImport(int $importLogId): array
    {
        $log = ImportLog::findOrFail($importLogId);
        if ($log->status !== 'completed') {
            return [
                'success' => false,
                'message' => 'Laporan import ini tidak berada dalam status completed atau sudah di-rollback.',
            ];
        }

        DB::beginTransaction();

        try {
            $createdIds = $log->created_ids;

            // Delete Student and Employee records first due to foreign keys to Users
            if (! empty($createdIds['students'])) {
                Student::whereIn('id', $createdIds['students'])->delete();
            }
            if (! empty($createdIds['employees'])) {
                Employee::whereIn('id', $createdIds['employees'])->delete();
            }

            // Delete associated Users
            if (! empty($createdIds['users'])) {
                User::whereIn('id', $createdIds['users'])->forceDelete(); // Force delete because it has soft delete
            }

            // Delete Subjects
            if (! empty($createdIds['subjects'])) {
                Subject::whereIn('id', $createdIds['subjects'])->delete();
            }

            // Delete Classrooms
            if (! empty($createdIds['classrooms'])) {
                Classroom::whereIn('id', $createdIds['classrooms'])->delete();
            }

            // Update Log
            $log->update(['status' => 'rolled_back']);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Rollback berhasil diselesaikan. Seluruh data batch telah dihapus secara aman.',
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal melakukan rollback: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Export module data to Excel (.xlsx), CSV, PDF, or JSON formats.
     */
    public function export(string $module, string $format)
    {
        $headings = [];
        $fileName = 'Ekspor_'.ucfirst($module).'_'.date('Ymd_His');
        $collection = collect();
        $mapCallback = fn ($row) => [];

        if ($module === 'students') {
            $collection = Student::with(['user', 'classroom'])->get();
            $headings = ['Nama Siswa', 'Email', 'NIS', 'NISN', 'Jenis Kelamin', 'Kelas', 'Alamat'];
            $mapCallback = fn ($student) => [
                $student->user->name,
                $student->user->email,
                $student->nis,
                $student->nisn,
                $student->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                $student->classroom ? $student->classroom->name : '-',
                $student->address ?: '-',
            ];

        } elseif ($module === 'employees') {
            $collection = Employee::with(['user'])->get();
            $headings = ['Nama Pegawai', 'Email', 'No Telepon', 'Role / Jabatan', 'NIP / NIY', 'Jabatan Kerja', 'Status Kerja', 'Gaji Pokok'];
            $mapCallback = fn ($emp) => [
                $emp->user->name,
                $emp->user->email,
                $emp->user->phone_number ?: '-',
                ucfirst($emp->user->role),
                $emp->employee_number ?: '-',
                $emp->position,
                strtoupper($emp->employment_status),
                number_format($emp->base_salary, 0, ',', '.'),
            ];

        } elseif ($module === 'subjects') {
            $collection = Subject::all();
            $headings = ['Kode Mapel', 'Nama Mata Pelajaran', 'Tingkat Kelas', 'KKM'];
            $mapCallback = fn ($sub) => [
                $sub->code,
                $sub->name,
                $sub->grade_level,
                $sub->kkm,
            ];

        } elseif ($module === 'classrooms') {
            $collection = Classroom::all();
            $headings = ['Nama Kelas', 'Tingkat Kelas', 'Jurusan', 'Tahun Akademik'];
            $mapCallback = fn ($class) => [
                $class->name,
                $class->grade_level,
                $class->major,
                $class->academic_year,
            ];
        }

        // Handle JSON format
        if ($format === 'json') {
            $data = $collection->map(function ($row) use ($mapCallback) {
                return $mapCallback($row);
            });

            return response()->json([
                'module' => $module,
                'exported_at' => now()->toIso8601String(),
                'headings' => $headings,
                'data' => $data,
            ]);
        }

        // Handle PDF format (Barryvdh DomPDF)
        if ($format === 'pdf') {
            $data = $collection->map(function ($row) use ($mapCallback) {
                return $mapCallback($row);
            });
            $title = 'Laporan Data '.ucfirst($module);
            $pdf = Pdf::loadView('import-export.pdf_export', compact('data', 'headings', 'title'));

            return $pdf->setPaper('a4', 'landscape')->download($fileName.'.pdf');
        }

        // Handle Excel / CSV format (Maatwebsite Excel)
        $excelFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $ext = $format === 'csv' ? '.csv' : '.xlsx';

        return Excel::download(new GenericExport($collection, $headings, $mapCallback), $fileName.$ext, $excelFormat);
    }
}
