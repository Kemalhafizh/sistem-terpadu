<?php

namespace App\Services\Integration;

use App\Exports\DapodikExport;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Student;

// Re-use DapodikExport layout since it's universally customizable

class EmisService
{
    /**
     * Map student records to EMIS compatible structure (with religious metrics).
     */
    public function exportStudentData(?int $classroomId = null): array
    {
        $query = Student::with(['user', 'classroom', 'tahfidzLogs']);
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }
        $students = $query->get();

        $mapped = [];
        foreach ($students as $student) {
            $activeYear = AcademicYear::where('is_active', true)->first();
            $target = $activeYear ? $student->tahfidzTargets()->where('academic_year_id', $activeYear->id)->first() : null;
            $totalJuz = $target ? $target->achieved_juz : ($student->tahfidzLogs()->whereNotNull('juz')->max('juz') ?? 0);

            $mapped[] = [
                'nisn' => $student->nisn ?? '',
                'nama' => $student->name ?? '',
                'gender' => $student->gender === 'P' ? 'Perempuan' : 'Laki-laki',
                'kelas' => $student->classroom?->name ?? '',
                'capaian_tahfidz' => $totalJuz.' Juz',
            ];
        }

        return $mapped;
    }

    /**
     * Map teacher records to EMIS compatible structure.
     */
    public function exportTeacherData(): array
    {
        $teachers = Employee::with('user')->get();

        $mapped = [];
        foreach ($teachers as $teacher) {
            $mapped[] = [
                'nip' => $teacher->employee_number ?? '',
                'nama' => $teacher->name ?? '',
                'jabatan' => $teacher->position ?? '',
                'status_kepegawaian' => $teacher->employment_status ?? '',
                'sertifikasi' => 'Sudah Sertifikasi', // Standard mock response for school profile
            ];
        }

        return $mapped;
    }

    /**
     * Map classroom records to EMIS compatible structure.
     */
    public function exportClassroomData(): array
    {
        $classrooms = Classroom::with('teacher')->get();

        $mapped = [];
        foreach ($classrooms as $classroom) {
            $mapped[] = [
                'nama_kelas' => $classroom->name ?? '',
                'tingkat' => $classroom->grade_level ?? '',
                'wali_kelas' => $classroom->teacher?->name ?? '',
                'tahun_pelajaran' => $classroom->academic_year ?? '',
            ];
        }

        return $mapped;
    }

    /**
     * Generate dynamic export object for EMIS.
     */
    public function generateEmisExcel(string $type, ?int $classroomId = null): DapodikExport
    {
        $data = [];
        $headings = [];
        $title = 'Ekspor EMIS Kemenag';

        if ($type === 'students') {
            $data = $this->exportStudentData($classroomId);
            $headings = ['NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Rombel Kelas', 'Level Gamifikasi', 'Total XP', 'Capaian Tahfidz', 'Status Keagamaan'];
            $title = 'Siswa EMIS';
        } elseif ($type === 'teachers') {
            $data = $this->exportTeacherData();
            $headings = ['NIP/NUPTK', 'Nama Lengkap', 'Jabatan', 'Status Pegawai', 'Status Sertifikasi'];
            $title = 'Guru EMIS';
        } elseif ($type === 'classrooms') {
            $data = $this->exportClassroomData();
            $headings = ['Nama Rombel', 'Tingkat', 'Wali Kelas', 'Tahun Pelajaran'];
            $title = 'Kelas EMIS';
        }

        return new DapodikExport($data, $headings, $title);
    }
}
