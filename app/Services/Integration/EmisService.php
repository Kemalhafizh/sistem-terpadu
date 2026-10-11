<?php

namespace App\Services\Integration;

use App\Exports\DapodikExport;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Student;
use App\Models\TahfidzLog;

// Re-use DapodikExport layout since it's universally customizable

class EmisService
{
    /**
     * Map student records to EMIS compatible structure.
     *
     * Capaian tahfidz is the number of distinct juz the student has deposited
     * (ziyadah), as TahfidzService counts it, and empty when there are no
     * deposits. It used to be the highest juz number (juz 30 read as "30 Juz")
     * and "0 Juz" for students with no data.
     */
    public function exportStudentData(?int $classroomId = null): array
    {
        $query = Student::with(['user', 'classroom']);
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }
        $students = $query->get();

        $juz = TahfidzLog::whereIn('student_id', $students->pluck('id'))
            ->where('type', 'ziyadah')
            ->whereNotNull('juz')
            ->selectRaw('student_id, COUNT(DISTINCT juz) as juz_count')
            ->groupBy('student_id')
            ->pluck('juz_count', 'student_id');

        $mapped = [];
        foreach ($students as $student) {
            $mapped[] = [
                'nisn' => $student->nisn ?? '',
                'nama' => $student->name ?? '',
                'gender' => match ($student->gender) {
                    'P' => 'Perempuan',
                    'L' => 'Laki-laki',
                    default => '',
                },
                'kelas' => $student->classroom?->name ?? '',
                'capaian_tahfidz' => isset($juz[$student->id]) ? $juz[$student->id].' Juz' : '',
            ];
        }

        return $mapped;
    }

    /**
     * Map teacher records to EMIS compatible structure.
     *
     * "Sertifikasi" is "Sudah" only when HRD holds a sertifikasi document for
     * the employee, and empty otherwise. Every teacher used to be exported as
     * "Sudah Sertifikasi".
     */
    public function exportTeacherData(): array
    {
        $teachers = Employee::with('user')
            ->withExists(['documents as has_certification' => fn ($q) => $q->where('type', 'sertifikasi')])
            ->get();

        $mapped = [];
        foreach ($teachers as $teacher) {
            $mapped[] = [
                'nuptk' => $teacher->nuptk ?? '',
                'nama' => $teacher->name ?? '',
                'jabatan' => $teacher->position ?? '',
                'status_kepegawaian' => $teacher->employment_status ?? '',
                'sertifikasi' => $teacher->has_certification ? 'Sudah' : '',
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
            // One heading per exported column (there used to be eight headings for five columns).
            $headings = ['NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Rombel Kelas', 'Capaian Tahfidz'];
            $title = 'Siswa EMIS';
        } elseif ($type === 'teachers') {
            $data = $this->exportTeacherData();
            $headings = ['NUPTK', 'Nama Lengkap', 'Jabatan', 'Status Pegawai', 'Status Sertifikasi'];
            $title = 'Guru EMIS';
        } elseif ($type === 'classrooms') {
            $data = $this->exportClassroomData();
            $headings = ['Nama Rombel', 'Tingkat', 'Wali Kelas', 'Tahun Pelajaran'];
            $title = 'Kelas EMIS';
        }

        return new DapodikExport($data, $headings, $title);
    }
}
