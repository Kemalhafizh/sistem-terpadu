<?php

namespace App\Services\Integration;

use App\Exports\DapodikExport;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Student;
use Carbon\Carbon;

class DapodikService
{
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
            $mapped[] = [
                'nisn' => $student->nisn ?? '',
                'nis' => $student->nis ?? '',
                'nama' => $student->name ?? '',
                'gender' => $student->gender === 'P' ? 'P' : 'L',
                'tempat_lahir' => 'Semarang', // Default fallback
                'tanggal_lahir' => $student->birth_date ? Carbon::parse($student->birth_date)->toDateString() : '',
                'alamat' => $student->address ?? '',
                'kelas' => $student->classroom?->name ?? 'Belum ada kelas',
                'ibu_kandung' => 'Ibu Kandung Siswa', // Fallback standard
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
            $mapped[] = [
                'nuptk' => $teacher->employee_number ?? '',
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

                if (empty($row['tanggal_lahir'])) {
                    $rowErrors[] = 'Tanggal Lahir wajib diisi.';
                }

                if ($row['kelas'] === 'Belum ada kelas') {
                    $rowErrors[] = 'Siswa belum dimasukkan ke Rombel Kelas.';
                }
            } elseif ($type === 'teachers') {
                if (empty($row['nuptk'])) {
                    $rowErrors[] = 'NIP / NUPTK wajib diisi.';
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
            $headings = ['NIP/NUPTK', 'Nama Lengkap', 'Email', 'Jabatan', 'Status Kepegawaian', 'Tanggal Rekrut'];
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
}
