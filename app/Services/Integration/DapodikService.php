<?php

namespace App\Services\Integration;

use App\Exports\DapodikExport;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\IntegrationConfig;
use App\Models\IntegrationLog;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Services\Academic\EnrollmentService;
use App\Services\Auth\AccountProvisioningService;
use App\Services\Auth\UserIdentifierResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class DapodikService
{
    public function __construct(
        protected ?AccountProvisioningService $provisioningService = null,
        protected ?EnrollmentService $enrollmentService = null,
        protected ?UserIdentifierResolver $identifierResolver = null,
    ) {}

    private function provisioning(): AccountProvisioningService
    {
        return $this->provisioningService ??= app(AccountProvisioningService::class);
    }

    private function enrollment(): EnrollmentService
    {
        return $this->enrollmentService ??= app(EnrollmentService::class);
    }

    private function resolver(): UserIdentifierResolver
    {
        return $this->identifierResolver ??= app(UserIdentifierResolver::class);
    }

    /**
     * Map student records to Dapodik compatible structure.
     */
    public function exportStudentData(?int $classroomId = null): array
    {
        $query = Student::with(['user', 'classroom']);
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }
        $students = $query->get();

        $mapped = [];
        foreach ($students as $student) {
            // Only what the school recorded. An empty cell is flagged by
            // validateAgainstDapodik(); it used to be "Semarang" and
            // "Ibu Kandung Siswa" for every student.
            $mapped[] = [
                'nisn' => $student->nisn ?? '',
                'nis' => $student->nis ?? '',
                'nama' => $student->name ?? '',
                'gender' => in_array($student->gender, ['L', 'P'], true) ? $student->gender : '',
                'tempat_lahir' => $student->birth_place ?? '',
                'tanggal_lahir' => $student->birth_date ? Carbon::parse($student->birth_date)->toDateString() : '',
                'alamat' => $student->address ?? '',
                'kelas' => $student->classroom?->name ?? '',
                'ibu_kandung' => $student->mother_name ?? '',
            ];
        }

        return $mapped;
    }

    /**
     * Map teacher/employee records to Dapodik compatible structure.
     */
    public function exportTeacherData(): array
    {
        $teachers = Employee::with('user')->get();

        $mapped = [];
        foreach ($teachers as $teacher) {
            // NUPTK is the national number; the foundation's NIY used to be exported under it.
            $mapped[] = [
                'nuptk' => $teacher->nuptk ?? '',
                'niy' => $teacher->employee_number ?? '',
                'nama' => $teacher->name ?? '',
                'email' => $teacher->email ?? '',
                'jabatan' => $teacher->position ?? '',
                'status_kepegawaian' => $teacher->employment_status ?? '',
                'tanggal_masuk' => $teacher->hire_date ? Carbon::parse($teacher->hire_date)->toDateString() : '',
            ];
        }

        return $mapped;
    }

    /**
     * Map classroom records to Dapodik compatible structure.
     */
    public function exportClassroomData(): array
    {
        $classrooms = Classroom::with('teacher')->get();

        $mapped = [];
        foreach ($classrooms as $classroom) {
            $mapped[] = [
                'nama_kelas' => $classroom->name ?? '',
                'tingkat_kelas' => $classroom->grade_level ?? '',
                'jurusan' => $classroom->major ?? '',
                'wali_kelas' => $classroom->teacher?->name ?? '',
                'tahun_ajaran' => $classroom->academic_year ?? '',
            ];
        }

        return $mapped;
    }

    /**
     * Map attendance data to Dapodik compatible structure.
     * $month parameter in format YYYY-MM
     */
    public function exportAttendanceData(string $month, ?int $classroomId = null): array
    {
        $query = Student::with(['user', 'classroom']);
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }
        $students = $query->get();

        $startOfMonth = Carbon::parse($month.'-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month.'-01')->endOfMonth();

        $mapped = [];
        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->get();

            $h = $attendances->where('status', 'H')->count();
            $s = $attendances->where('status', 'S')->count();
            $i = $attendances->where('status', 'I')->count();
            $a = $attendances->where('status', 'A')->count();

            $mapped[] = [
                'nisn' => $student->nisn ?? '',
                'nama' => $student->name ?? '',
                'kelas' => $student->classroom?->name ?? '',
                'hadir' => $h,
                'sakit' => $s,
                'izin' => $i,
                'alpa' => $a,
            ];
        }

        return $mapped;
    }

    /**
     * Validate compiled data against Dapodik required fields rules.
     */
    public function validateAgainstDapodik(string $type, array $data): array
    {
        $warnings = [];

        foreach ($data as $idx => $row) {
            $rowErrors = [];

            if ($type === 'students') {
                if (empty($row['nisn'])) {
                    $rowErrors[] = 'NISN wajib diisi.';
                } elseif (! preg_match('/^[0-9]{10}$/', $row['nisn'])) {
                    $rowErrors[] = 'NISN harus tepat 10 digit angka.';
                }

                if (empty($row['nama'])) {
                    $rowErrors[] = 'Nama Lengkap wajib diisi.';
                }

                if (empty($row['gender'])) {
                    $rowErrors[] = 'Jenis Kelamin wajib diisi.';
                }

                if (empty($row['tempat_lahir'])) {
                    $rowErrors[] = 'Tempat Lahir wajib diisi.';
                }

                if (empty($row['tanggal_lahir'])) {
                    $rowErrors[] = 'Tanggal Lahir wajib diisi.';
                }

                if (empty($row['ibu_kandung'])) {
                    $rowErrors[] = 'Nama Ibu Kandung wajib diisi.';
                }

                if (empty($row['kelas'])) {
                    $rowErrors[] = 'Siswa belum dimasukkan ke Rombel Kelas.';
                }
            } elseif ($type === 'teachers') {
                if (empty($row['nuptk'])) {
                    $rowErrors[] = 'NUPTK belum diisi.';
                }
                if (empty($row['nama'])) {
                    $rowErrors[] = 'Nama Lengkap wajib diisi.';
                }
                if (empty($row['email'])) {
                    $rowErrors[] = 'Alamat Email wajib diisi.';
                }
            }

            if (! empty($rowErrors)) {
                $warnings[] = [
                    'row' => $idx + 1,
                    'identifier' => $row['nama'] ?? ('Baris '.($idx + 1)),
                    'errors' => $rowErrors,
                ];
            }
        }

        return $warnings;
    }

    /**
     * Generate dynamic export object for controller.
     */
    public function generateDapodikExcel(string $type, ?int $classroomId = null, ?string $month = null): DapodikExport
    {
        $data = [];
        $headings = [];
        $title = 'Ekspor Dapodik';

        if ($type === 'students') {
            $data = $this->exportStudentData($classroomId);
            $headings = ['NISN', 'NIS', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Rombel Kelas', 'Nama Ibu Kandung'];
            $title = 'Data Siswa Dapodik';
        } elseif ($type === 'teachers') {
            $data = $this->exportTeacherData();
            $headings = ['NUPTK', 'NIY', 'Nama Lengkap', 'Email', 'Jabatan', 'Status Kepegawaian', 'Tanggal Rekrut'];
            $title = 'Data PTK Dapodik';
        } elseif ($type === 'classrooms') {
            $data = $this->exportClassroomData();
            $headings = ['Nama Rombel', 'Tingkat Kelas', 'Jurusan', 'Wali Kelas', 'Tahun Ajaran'];
            $title = 'Data Rombel Dapodik';
        } elseif ($type === 'attendances') {
            $month = $month ?? Carbon::now()->format('Y-m');
            $data = $this->exportAttendanceData($month, $classroomId);
            $headings = ['NISN', 'Nama Siswa', 'Rombel Kelas', 'Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)'];
            $title = 'Data Kehadiran '.$month;
        }

        return new DapodikExport($data, $headings, $title);
    }

    /**
     * K1: Import initial or delta data from Dapodik Excel/CSV export (`classrooms`, `teachers`, `students`).
     * Unmatched or invalid rows are reported explicitly in `failed_rows` and never silently ignored.
     *
     * @return array{
     *     type: string,
     *     total_rows: int,
     *     processed: int,
     *     created: int,
     *     updated: int,
     *     failed_count: int,
     *     failed_rows: list<array{row: int, identifier: string, errors: list<string>}>,
     *     credentials: list<array{role_label: string, name: string, identifier: string, temp_password: string, notes: string}>,
     *     log_id: int
     * }
     */
    public function importDapodik(
        string $type,
        string $filePath,
        bool $createAccounts = true,
        ?User $actor = null,
        ?string $extension = null,
    ): array {
        if (! in_array($type, ['classrooms', 'teachers', 'students'], true)) {
            throw new \InvalidArgumentException(__('Jenis impor Dapodik harus berupa classrooms, teachers, atau students.'));
        }

        $rows = $this->readSpreadsheetRows($filePath, $extension);
        if (count($rows) < 2) {
            throw new \InvalidArgumentException(__('File impor kosong atau tidak memiliki baris data di bawah header.'));
        }

        $headerRow = array_shift($rows) ?? [];
        $headers = array_map(fn ($value) => $this->normalizeHeader((string) ($value ?? '')), $headerRow);

        $activeSchoolYear = SchoolYear::active();
        $currentAcademicYear = AcademicYear::current();
        $defaultAcademicYear = $activeSchoolYear?->name
            ?? $currentAcademicYear?->year
            ?? (now()->year.'/'.(now()->year + 1));

        $created = 0;
        $updated = 0;
        $totalRows = 0;
        $failedRows = [];
        $credentials = [];

        foreach ($rows as $index => $rawRow) {
            if ($this->isBlankRow($rawRow)) {
                continue;
            }

            $totalRows++;
            $lineNumber = $index + 2;
            $mapped = $this->mapRowByAliases($rawRow, $headers, $this->aliasesForType($type));

            try {
                $rowCredentials = [];

                DB::transaction(function () use (
                    $type,
                    $mapped,
                    $createAccounts,
                    $actor,
                    $activeSchoolYear,
                    $currentAcademicYear,
                    $defaultAcademicYear,
                    &$created,
                    &$updated,
                    &$rowCredentials
                ) {
                    if ($type === 'classrooms') {
                        $status = $this->importClassroomRow($mapped, $activeSchoolYear, $defaultAcademicYear);
                    } elseif ($type === 'teachers') {
                        $status = $this->importTeacherRow($mapped, $createAccounts, $rowCredentials);
                    } else {
                        $status = $this->importStudentRow(
                            $mapped,
                            $createAccounts,
                            $actor,
                            $activeSchoolYear,
                            $currentAcademicYear,
                            $rowCredentials
                        );
                    }

                    if ($status === 'created') {
                        $created++;
                    } else {
                        $updated++;
                    }
                });

                foreach ($rowCredentials as $credential) {
                    $credentials[] = $credential;
                }
            } catch (Throwable $e) {
                $identifier = trim((string) (
                    $mapped['name']
                    ?? $mapped['nisn']
                    ?? $mapped['nis']
                    ?? $mapped['nuptk']
                    ?? $mapped['employee_number']
                    ?? ("Baris {$lineNumber}")
                ));

                $failedRows[] = [
                    'row' => $lineNumber,
                    'identifier' => $identifier !== '' ? $identifier : "Baris {$lineNumber}",
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        $config = IntegrationConfig::firstOrCreate(
            ['provider' => 'dapodik'],
            ['name' => 'Koneksi Dapodik SMA ISA 1', 'is_active' => true]
        );

        $processed = $created + $updated;
        $failedCount = count($failedRows);
        $logStatus = $failedCount === 0
            ? 'success'
            : ($processed > 0 ? 'partial' : 'failed');

        $log = IntegrationLog::create([
            'config_id' => $config->id,
            'direction' => 'import',
            'status' => $logStatus,
            'records_processed' => $processed,
            'records_failed' => $failedCount,
            'error_details' => $failedRows,
            'summary' => "Impor Dapodik {$type}: {$created} baru, {$updated} diperbarui, {$failedCount} gagal.",
        ]);

        $config->update(['last_sync_at' => now()]);

        return [
            'type' => $type,
            'total_rows' => $totalRows,
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'failed_count' => $failedCount,
            'failed_rows' => $failedRows,
            'credentials' => $credentials,
            'log_id' => $log->id,
        ];
    }

    /**
     * @return 'created'|'updated'
     */
    private function importClassroomRow(array $row, ?SchoolYear $activeSchoolYear, string $defaultAcademicYear): string
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException(__('Nama rombel wajib diisi.'));
        }

        $gradeLevel = $this->normalizeGradeLevel((string) ($row['grade_level'] ?? ''));
        if ($gradeLevel === null) {
            throw new \RuntimeException(__('Tingkat kelas harus berupa X, XI, atau XII (atau 10/11/12).'));
        }

        $major = trim((string) ($row['major'] ?? ''));
        if ($major === '') {
            $major = 'Umum';
        }

        $academicYear = trim((string) ($row['academic_year'] ?? ''));
        if ($academicYear === '') {
            $academicYear = $defaultAcademicYear;
        }

        $teacherId = $this->resolveTeacherUserId(
            trim((string) ($row['teacher_identifier'] ?? '')),
            trim((string) ($row['teacher_name'] ?? ''))
        );

        $existing = Classroom::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where('academic_year', $academicYear)
            ->first();

        if ($existing) {
            $updates = [
                'grade_level' => $gradeLevel,
                'major' => $major,
            ];
            if ($activeSchoolYear && $academicYear === $activeSchoolYear->name) {
                $updates['school_year_id'] = $activeSchoolYear->id;
            }
            if ($teacherId !== null) {
                $updates['teacher_id'] = $teacherId;
            }
            $existing->update($updates);

            return 'updated';
        }

        Classroom::create([
            'school_year_id' => $activeSchoolYear && $academicYear === $activeSchoolYear->name ? $activeSchoolYear->id : null,
            'name' => $name,
            'grade_level' => $gradeLevel,
            'major' => $major,
            'teacher_id' => $teacherId,
            'academic_year' => $academicYear,
        ]);

        return 'created';
    }

    /**
     * @param  list<array<string, string>>  $credentials
     * @return 'created'|'updated'
     */
    private function importTeacherRow(array $row, bool $createAccounts, array &$credentials): string
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException(__('Nama lengkap PTK wajib diisi.'));
        }

        $nuptk = $this->cleanIdentifier($row['nuptk'] ?? null);
        $employeeNumber = $this->cleanIdentifier($row['employee_number'] ?? null);
        $nik = $this->cleanIdentifier($row['nik'] ?? null);
        $email = $this->sanitizeEmail($row['email'] ?? null);
        $phone = $this->resolver()->normalizeLocalPhone($row['phone_number'] ?? null);

        if ($nuptk === null && $employeeNumber === null && $nik === null && $email === null && $phone === null) {
            throw new \RuntimeException(__('Minimal satu identitas unik PTK (NUPTK, NIY/NIP, NIK, Email, atau No. HP) wajib diisi.'));
        }

        $position = trim((string) ($row['position'] ?? ''));
        if ($position === '') {
            $position = 'Guru Mata Pelajaran';
        }

        $employmentStatus = $this->normalizeEmploymentStatus((string) ($row['employment_status'] ?? ''));
        $hireDate = $this->parseDateValue($row['hire_date'] ?? null);

        $existing = $this->findExistingEmployee($nuptk, $employeeNumber, $nik, $email, $phone);
        if ($existing) {
            $user = $existing->user;
            if ($user) {
                $userUpdates = ['name' => $name];
                if ($email !== null && ($user->email === null || $user->email === $email) && ! User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
                    $userUpdates['email'] = $email;
                }
                if ($phone !== null && blank($user->phone_number)) {
                    $userUpdates['phone_number'] = $phone;
                }
                $user->update($userUpdates);
            }

            $empUpdates = [
                'position' => $position,
                'employment_status' => $employmentStatus,
            ];
            if ($nuptk !== null && ! Employee::where('nuptk', $nuptk)->where('id', '!=', $existing->id)->exists()) {
                $empUpdates['nuptk'] = $nuptk;
            }
            if ($employeeNumber !== null && ! Employee::where('employee_number', $employeeNumber)->where('id', '!=', $existing->id)->exists()) {
                $empUpdates['employee_number'] = $employeeNumber;
            }
            if ($nik !== null) {
                $empUpdates['nik'] = $nik;
            }
            if ($hireDate !== null) {
                $empUpdates['hire_date'] = $hireDate;
            }
            $existing->update($empUpdates);

            return 'updated';
        }

        $user = null;
        if ($email !== null) {
            $user = User::where('email', $email)->first();
        }
        if (! $user && $phone !== null) {
            $user = User::whereIn('phone_number', $this->resolver()->phoneVariants($phone))->first();
        }

        if (! $user) {
            if (! $createAccounts) {
                throw new \RuntimeException(__('Akun pengguna PTK belum tersedia dan opsi pembuatan akun otomatis dinonaktifkan.'));
            }

            [$role, $accountType] = $this->inferStaffRoleAndType($position);
            $tempPassword = $this->provisioning()->generateTemporaryPassword();
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone_number' => $phone,
                'password' => $tempPassword,
                'role' => $role,
                'account_type' => $accountType,
                'must_change_password' => true,
            ]);

            $credentials[] = [
                'role_label' => 'PTK / '.ucfirst(str_replace('_', ' ', $role)),
                'name' => $user->name,
                'identifier' => $employeeNumber ?: ($nuptk ?: ($phone ?: ($email ?: '-'))),
                'temp_password' => $tempPassword,
                'notes' => $position,
            ];
        }

        if ($employeeNumber !== null && Employee::where('employee_number', $employeeNumber)->exists()) {
            throw new \RuntimeException(__('NIY/NIP :num sudah dipakai pegawai lain.', ['num' => $employeeNumber]));
        }

        if ($nuptk !== null && Employee::where('nuptk', $nuptk)->exists()) {
            throw new \RuntimeException(__('NUPTK :nuptk sudah dipakai pegawai lain.', ['nuptk' => $nuptk]));
        }

        Employee::create([
            'user_id' => $user->id,
            'employee_number' => $employeeNumber,
            'nuptk' => $nuptk,
            'nik' => $nik,
            'position' => $position,
            'employment_status' => $employmentStatus,
            'hire_date' => $hireDate,
        ]);

        return 'created';
    }

    /**
     * @param  list<array<string, string>>  $credentials
     * @return 'created'|'updated'
     */
    private function importStudentRow(
        array $row,
        bool $createAccounts,
        ?User $actor,
        ?SchoolYear $activeSchoolYear,
        ?AcademicYear $currentAcademicYear,
        array &$credentials,
    ): string {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException(__('Nama lengkap siswa wajib diisi.'));
        }

        $nis = $this->cleanIdentifier($row['nis'] ?? null);
        $nisn = $this->cleanIdentifier($row['nisn'] ?? null);
        if ($nis === null || $nisn === null) {
            throw new \RuntimeException(__('NIS dan NISN wajib diisi untuk sinkronisasi Dapodik/e-Rapor.'));
        }

        $gender = $this->normalizeGender((string) ($row['gender'] ?? ''));
        if ($gender === null) {
            throw new \RuntimeException(__('Jenis kelamin harus diisi L/P (Laki-laki/Perempuan).'));
        }

        $classroomName = trim((string) ($row['classroom_name'] ?? ''));
        $classroom = null;
        if ($classroomName !== '') {
            $classroom = $this->findClassroomByName($classroomName, $activeSchoolYear, $currentAcademicYear);
            if (! $classroom) {
                throw new \RuntimeException(__("Rombel ':name' tidak ditemukan. Impor atau buat data Rombel terlebih dahulu.", [
                    'name' => $classroomName,
                ]));
            }
        }

        $byNisn = Student::where('nisn', $nisn)->first();
        $byNis = Student::where('nis', $nis)->first();
        if ($byNisn && $byNis && $byNisn->id !== $byNis->id) {
            throw new \RuntimeException(__('Konflik data: NISN :nisn dan NIS :nis tercatat pada dua siswa berbeda.', [
                'nisn' => $nisn,
                'nis' => $nis,
            ]));
        }

        $existing = $byNisn ?? $byNis;
        $nik = $this->cleanIdentifier($row['nik'] ?? null);
        $email = $this->sanitizeEmail($row['email'] ?? null);
        $phone = $this->resolver()->normalizeLocalPhone($row['phone_number'] ?? null);
        $birthPlace = trim((string) ($row['birth_place'] ?? '')) ?: null;
        $birthDate = $this->parseDateValue($row['birth_date'] ?? null);
        $address = trim((string) ($row['address'] ?? '')) ?: null;
        $motherName = trim((string) ($row['mother_name'] ?? '')) ?: null;
        $parentName = trim((string) ($row['parent_name'] ?? '')) ?: null;
        $parentPhone = $this->resolver()->normalizeLocalPhone($row['parent_phone'] ?? null);
        $parentEmail = $this->sanitizeEmail($row['parent_email'] ?? null);

        $actionStatus = 'created';

        if ($existing) {
            $actionStatus = 'updated';
            $studentUser = $existing->user;
            if ($studentUser) {
                $userUpdates = ['name' => $name];
                if ($email !== null && ! User::where('email', $email)->where('id', '!=', $studentUser->id)->exists()) {
                    $userUpdates['email'] = $email;
                }
                if ($phone !== null && blank($studentUser->phone_number)) {
                    $userUpdates['phone_number'] = $phone;
                }
                $studentUser->update($userUpdates);
            }

            $studentUpdates = [
                'nis' => $nis,
                'nisn' => $nisn,
                'gender' => $gender,
            ];
            if ($nik !== null) {
                $studentUpdates['nik'] = $nik;
            }
            if ($birthPlace !== null) {
                $studentUpdates['birth_place'] = $birthPlace;
            }
            if ($birthDate !== null) {
                $studentUpdates['birth_date'] = $birthDate;
            }
            if ($address !== null) {
                $studentUpdates['address'] = $address;
            }
            if ($motherName !== null) {
                $studentUpdates['mother_name'] = $motherName;
            }
            if ($classroom !== null) {
                $studentUpdates['classroom_id'] = $classroom->id;
            }

            $existing->update($studentUpdates);
            $student = $existing;
        } else {
            if ($email !== null) {
                $existingEmailUser = User::where('email', $email)->first();
                if ($existingEmailUser && $existingEmailUser->student()->exists()) {
                    throw new \RuntimeException(__('Email :email sudah terhubung dengan akun siswa lain.', ['email' => $email]));
                }
            }

            $studentUser = $email !== null ? User::where('email', $email)->first() : null;
            if (! $studentUser) {
                if (! $createAccounts) {
                    throw new \RuntimeException(__('Akun siswa belum tersedia dan opsi buat akun otomatis dinonaktifkan.'));
                }

                $provisionedStudent = $this->provisioning()->ensureStudentUser($name, $email, $phone);
                $studentUser = $provisionedStudent['user'];

                if ($provisionedStudent['created'] && filled($provisionedStudent['plain_password'])) {
                    $credentials[] = [
                        'role_label' => 'Siswa',
                        'name' => $studentUser->name,
                        'identifier' => $nisn.' / '.$nis,
                        'temp_password' => (string) $provisionedStudent['plain_password'],
                        'notes' => $classroom?->name ?? 'Belum diplotting',
                    ];
                }
            }

            $student = Student::create([
                'user_id' => $studentUser->id,
                'classroom_id' => $classroom?->id,
                'nis' => $nis,
                'nisn' => $nisn,
                'nik' => $nik,
                'gender' => $gender,
                'birth_place' => $birthPlace,
                'birth_date' => $birthDate,
                'address' => $address,
                'mother_name' => $motherName,
                'status' => 'active',
            ]);
        }

        if ($classroom !== null && $activeSchoolYear !== null) {
            $this->enrollment()->enroll(
                $student,
                $classroom,
                $activeSchoolYear,
                $activeSchoolYear->start_date,
                $actor
            );
        }

        if ($createAccounts && ($parentPhone !== null || $parentEmail !== null)) {
            $parentResult = $this->provisioning()->ensureParentForStudent(
                $student,
                $parentName ?: ('Wali '.$name),
                $parentPhone,
                $parentEmail
            );

            if ($parentResult && $parentResult['created'] && filled($parentResult['plain_password'])) {
                $credentials[] = [
                    'role_label' => 'Orang Tua / Wali',
                    'name' => $parentResult['user']->name,
                    'identifier' => (string) ($parentResult['user']->phone_number ?: $parentResult['user']->email ?: '-'),
                    'temp_password' => (string) $parentResult['plain_password'],
                    'notes' => 'Wali dari '.$name.' ('.$nis.')',
                ];
            }
        }

        return $actionStatus;
    }

    /**
     * @return list<array<int, mixed>>
     */
    private function readSpreadsheetRows(string $filePath, ?string $extension = null): array
    {
        if (! is_file($filePath)) {
            throw new \InvalidArgumentException(__('File impor tidak ditemukan.'));
        }

        $ext = strtolower(trim((string) ($extension ?: pathinfo($filePath, PATHINFO_EXTENSION)), '.'));

        if ($ext === 'csv') {
            $sampleLines = preg_split('/\r\n|\r|\n/', (string) preg_replace('/^\xEF\xBB\xBF/', '', (string) file_get_contents($filePath)));
            $firstLine = '';
            foreach ($sampleLines ?: [] as $line) {
                if (trim($line) !== '') {
                    $firstLine = $line;
                    break;
                }
            }
            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

            $reader = new Csv;
            $reader->setDelimiter($delimiter);
            $reader->setInputEncoding('UTF-8');

            return $reader->load($filePath)->getActiveSheet()->toArray(null, true, false, false);
        }

        if (! in_array($ext, ['xlsx', 'xls', 'ods'], true)) {
            throw new \InvalidArgumentException(__('Format file tidak didukung. Gunakan file Excel (.xlsx/.xls) atau CSV (.csv).'));
        }

        $tempCopy = null;
        $loadPath = $filePath;
        if (! str_ends_with(strtolower($filePath), '.'.$ext)) {
            $tempCopy = sys_get_temp_dir().'/dapodik_'.Str::uuid().'.'.$ext;
            copy($filePath, $tempCopy);
            $loadPath = $tempCopy;
        }

        try {
            return IOFactory::load($loadPath)->getActiveSheet()->toArray(null, true, false, false);
        } finally {
            if ($tempCopy !== null && is_file($tempCopy)) {
                @unlink($tempCopy);
            }
        }
    }

    private function normalizeHeader(string $header): string
    {
        $clean = strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', $header)));
        $clean = (string) preg_replace('/[\s\-\/\.]+/', '_', $clean);
        $clean = (string) preg_replace('/[^a-z0-9_]/', '', $clean);

        return trim($clean, '_');
    }

    private function isBlankRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) ($cell ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, list<string>>  $aliases
     * @return array<string, mixed>
     */
    private function mapRowByAliases(array $rawRow, array $headers, array $aliases): array
    {
        $byHeader = [];
        foreach ($headers as $colIdx => $headerName) {
            if ($headerName === '') {
                continue;
            }
            $byHeader[$headerName] = $rawRow[$colIdx] ?? null;
        }

        $mapped = [];
        foreach ($aliases as $canonical => $candidates) {
            $mapped[$canonical] = null;
            foreach ($candidates as $candidate) {
                if (array_key_exists($candidate, $byHeader) && trim((string) ($byHeader[$candidate] ?? '')) !== '') {
                    $mapped[$canonical] = $byHeader[$candidate];
                    break;
                }
            }
        }

        return $mapped;
    }

    /**
     * @return array<string, list<string>>
     */
    private function aliasesForType(string $type): array
    {
        return match ($type) {
            'classrooms' => [
                'name' => ['nama_rombel', 'nama_kelas', 'rombel', 'kelas', 'nama', 'name'],
                'grade_level' => ['tingkat_kelas', 'tingkat', 'tingkat_pendidikan', 'grade_level', 'jenjang'],
                'major' => ['jurusan', 'major', 'program_keahlian', 'peminatan', 'kurikulum'],
                'teacher_name' => ['wali_kelas', 'guru_wali', 'nama_wali_kelas', 'wali'],
                'teacher_identifier' => ['nuptk_wali', 'niy_wali', 'email_wali', 'kode_wali'],
                'academic_year' => ['tahun_ajaran', 'tahun_pelajaran', 'academic_year'],
            ],
            'teachers' => [
                'nuptk' => ['nuptk'],
                'employee_number' => ['niy', 'niy_nigk', 'nip', 'nomor_induk', 'employee_number'],
                'nik' => ['nik', 'no_ktp', 'nomor_ktp'],
                'name' => ['nama_lengkap', 'nama_ptk', 'nama', 'name'],
                'email' => ['email', 'alamat_email', 'surel'],
                'phone_number' => ['no_hp', 'hp', 'no_telepon', 'telepon', 'whatsapp', 'phone_number'],
                'position' => ['jabatan', 'jenis_ptk', 'tugas_utama', 'position'],
                'employment_status' => ['status_kepegawaian', 'employment_status', 'status'],
                'hire_date' => ['tanggal_masuk', 'tanggal_rekrut', 'tmt', 'tmt_pengangkatan', 'hire_date'],
            ],
            default => [
                'nisn' => ['nisn'],
                'nis' => ['nis', 'nipd'],
                'nik' => ['nik', 'no_ktp'],
                'name' => ['nama_lengkap', 'nama_siswa', 'nama', 'name'],
                'gender' => ['jenis_kelamin', 'gender', 'jk', 'l_p'],
                'birth_place' => ['tempat_lahir', 'birth_place'],
                'birth_date' => ['tanggal_lahir', 'tgl_lahir', 'birth_date'],
                'address' => ['alamat', 'alamat_jalan', 'address'],
                'classroom_name' => ['rombel_kelas', 'rombel_saat_ini', 'nama_rombel', 'nama_kelas', 'rombel', 'kelas', 'classroom'],
                'mother_name' => ['nama_ibu_kandung', 'ibu_kandung', 'nama_ibu', 'mother_name'],
                'phone_number' => ['no_hp', 'hp', 'hp_siswa', 'no_hp_siswa', 'telepon', 'phone_number'],
                'email' => ['email', 'alamat_email', 'email_siswa'],
                'parent_name' => ['nama_wali', 'nama_orang_tua', 'orang_tua', 'nama_ayah', 'parent_name'],
                'parent_phone' => ['hp_ortu', 'hp_wali', 'no_hp_orang_tua', 'no_hp_ortu', 'no_hp_wali', 'telepon_ortu', 'parent_phone'],
                'parent_email' => ['email_ortu', 'email_wali', 'parent_email'],
            ],
        };
    }

    private function cleanIdentifier(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || $raw === '-') {
            return null;
        }

        if (preg_match('/^\d+\.0+$/', $raw)) {
            $raw = (string) preg_replace('/\.0+$/', '', $raw);
        }

        return $raw !== '' ? $raw : null;
    }

    private function normalizeGradeLevel(string $raw): ?string
    {
        $clean = strtoupper(trim((string) preg_replace('/^(kelas|tingkat)\s+/i', '', trim($raw))));

        return match ($clean) {
            'X', '10' => 'X',
            'XI', '11' => 'XI',
            'XII', '12' => 'XII',
            default => null,
        };
    }

    private function normalizeGender(string $raw): ?string
    {
        $clean = strtolower(trim($raw));
        if (in_array($clean, ['l', 'lk', 'laki-laki', 'laki laki', 'male', 'm', 'pria'], true)) {
            return 'L';
        }
        if (in_array($clean, ['p', 'pr', 'perempuan', 'wanita', 'female', 'f'], true)) {
            return 'P';
        }

        return null;
    }

    private function normalizeEmploymentStatus(string $raw): string
    {
        $clean = strtolower(trim($raw));
        if (str_contains($clean, 'gty') || str_contains($clean, 'pty')) {
            return 'gty';
        }
        if (str_contains($clean, 'staf_tetap') || str_contains($clean, 'pegawai tetap') || $clean === 'tetap') {
            return 'staf_tetap';
        }
        if (str_contains($clean, 'honor')) {
            return 'honorer';
        }

        return 'gtt';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function inferStaffRoleAndType(string $position): array
    {
        $lower = strtolower($position);
        if (str_contains($lower, 'tata usaha') || str_contains($lower, 'staf tu') || str_contains($lower, 'operator')) {
            return ['staf_tu', 'karyawan'];
        }
        if (str_contains($lower, 'pustaka')) {
            return ['pustakawan', 'karyawan'];
        }
        if (str_contains($lower, 'uks') || str_contains($lower, 'perawat')) {
            return ['petugas_uks', 'karyawan'];
        }
        if (str_contains($lower, 'bendahara')) {
            return ['bendahara', 'karyawan'];
        }
        if (str_contains($lower, 'bimbingan') || str_contains($lower, 'konseling') || preg_match('/\bbk\b/', $lower)) {
            return ['bk', 'guru'];
        }

        return ['guru', 'guru'];
    }

    private function parseDateValue(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            } catch (Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse(trim((string) $value))->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function sanitizeEmail(mixed $value): ?string
    {
        $clean = trim((string) ($value ?? ''));
        if ($clean === '' || ! filter_var($clean, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return strtolower($clean);
    }

    private function resolveTeacherUserId(string $identifier, string $name): ?int
    {
        if ($identifier !== '') {
            $byEmployee = Employee::where('nuptk', $identifier)
                ->orWhere('employee_number', $identifier)
                ->first();
            if ($byEmployee) {
                return $byEmployee->user_id;
            }

            $user = $this->resolver()->findByIdentifier($identifier);
            if ($user) {
                return $user->id;
            }
        }

        if ($name !== '') {
            $user = User::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->whereIn('role', ['guru', 'teacher', 'wali_kelas', 'bk', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas'])
                ->first();

            if ($user) {
                return $user->id;
            }
        }

        return null;
    }

    private function findExistingEmployee(
        ?string $nuptk,
        ?string $employeeNumber,
        ?string $nik,
        ?string $email,
        ?string $phone,
    ): ?Employee {
        if ($nuptk !== null) {
            $found = Employee::where('nuptk', $nuptk)->first();
            if ($found) {
                return $found;
            }
        }

        if ($employeeNumber !== null) {
            $found = Employee::where('employee_number', $employeeNumber)->first();
            if ($found) {
                return $found;
            }
        }

        if ($nik !== null) {
            $found = Employee::where('nik', $nik)->first();
            if ($found) {
                return $found;
            }
        }

        if ($email !== null) {
            $found = Employee::whereHas('user', fn ($q) => $q->where('email', $email))->first();
            if ($found) {
                return $found;
            }
        }

        if ($phone !== null) {
            $variants = $this->resolver()->phoneVariants($phone);
            $found = Employee::whereHas('user', fn ($q) => $q->whereIn('phone_number', $variants))->first();
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    private function findClassroomByName(
        string $name,
        ?SchoolYear $activeSchoolYear,
        ?AcademicYear $currentAcademicYear,
    ): ?Classroom {
        $lower = strtolower($name);

        if ($activeSchoolYear) {
            $match = Classroom::query()
                ->where('school_year_id', $activeSchoolYear->id)
                ->whereRaw('LOWER(name) = ?', [$lower])
                ->first();
            if ($match) {
                return $match;
            }
        }

        if ($currentAcademicYear) {
            $match = Classroom::query()
                ->where('academic_year', $currentAcademicYear->year)
                ->whereRaw('LOWER(name) = ?', [$lower])
                ->first();
            if ($match) {
                return $match;
            }
        }

        return Classroom::query()
            ->whereRaw('LOWER(name) = ?', [$lower])
            ->latest('id')
            ->first();
    }
}
