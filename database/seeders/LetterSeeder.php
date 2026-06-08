<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class LetterSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $stafTu = User::where('email', 'staf.tu@sultanagung1.sch.id')->first();
        if (!$stafTu) {
            $stafTu = $admin;
        }

        $students = Student::with(['user', 'classroom'])->take(10)->get();
        $employees = Employee::with('user')->take(5)->get();

        Letter::truncate();
        LetterTemplate::truncate();

        // 1. Seed 3 Templates
        $template1 = LetterTemplate::create([
            'name' => 'Surat Keterangan Aktif Siswa',
            'code' => 'SURAT_KETERANGAN_AKTIF',
            'body_template' => '<p>Yang bertanda tangan di bawah ini, Kepala Sekolah SMA Islam Sultan Agung 1 Semarang menerangkan bahwa:</p><table><tr><td>Nama</td><td>: {{student_name}}</td></tr><tr><td>NIS / NISN</td><td>: {{nis}} / {{nisn}}</td></tr><tr><td>Kelas</td><td>: {{classroom}}</td></tr></table><p>Adalah benar-benar siswa aktif di sekolah kami tahun ajaran {{academic_year}}.</p>',
            'required_fields' => ['student_name', 'nis', 'nisn', 'classroom', 'academic_year'],
            'signer_name' => 'KH. Ahmad Fauzi, S.Pd.I',
            'signer_title' => 'Kepala Sekolah',
            'is_active' => true,
        ]);

        $template2 = LetterTemplate::create([
            'name' => 'Surat Tugas Karyawan / Guru',
            'code' => 'SURAT_TUGAS',
            'body_template' => '<p>Kepala Sekolah SMA Islam Sultan Agung 1 Semarang memberikan tugas kepada:</p><table><tr><td>Nama</td><td>: {{employee_name}}</td></tr><tr><td>NIP/NIY</td><td>: {{employee_number}}</td></tr><tr><td>Jabatan</td><td>: {{position}}</td></tr></table><p>Untuk menghadiri/melaksanakan tugas: {{task_description}} pada tanggal {{task_date}}.</p>',
            'required_fields' => ['employee_name', 'employee_number', 'position', 'task_description', 'task_date'],
            'signer_name' => 'KH. Ahmad Fauzi, S.Pd.I',
            'signer_title' => 'Kepala Sekolah',
            'is_active' => true,
        ]);

        $template3 = LetterTemplate::create([
            'name' => 'Surat Undangan Wali Murid',
            'code' => 'SURAT_UNDANGAN',
            'body_template' => '<p>Kepada Yth. Wali Murid dari {{student_name}} Kelas {{classroom}}, di tempat.</p><p>Kami mengharap kehadiran Bapak/Ibu pada acara {{event_name}} yang akan dilaksanakan pada {{event_date}} bertempat di {{event_location}}.</p>',
            'required_fields' => ['student_name', 'classroom', 'event_name', 'event_date', 'event_location'],
            'signer_name' => 'Ust. Mahmud Hasan, S.Pd',
            'signer_title' => 'Waka Kesiswaan',
            'is_active' => true,
        ]);

        // 2. Seed 15 Letters
        // 5 Surat Keterangan Aktif (student_id)
        for ($i = 0; $i < 5; $i++) {
            $student = $students[$i] ?? $students->first();
            Letter::create([
                'template_id' => $template1->id,
                'letter_number' => '421.3/' . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . '/SMA-SA1/VI/' . date('Y'),
                'field_values' => [
                    'student_name' => $student->user->name ?? 'Siswa',
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'classroom' => $student->classroom->name ?? 'X-A',
                    'academic_year' => '2025/2026',
                ],
                'student_id' => $student->id,
                'created_by' => $stafTu->id,
                'issue_date' => now()->subDays(rand(1, 10))->format('Y-m-d'),
                'status' => ['draft', 'issued', 'revoked'][rand(0, 2)],
                'notes' => 'Surat keterangan aktif untuk keperluan administrasi tunjangan orang tua.',
            ]);
        }

        // 5 Surat Tugas (employee_id)
        for ($i = 0; $i < 5; $i++) {
            $employee = $employees[$i] ?? $employees->first();
            $tasks = [
                'Mengikuti Workshop Kurikulum Merdeka di Dinas Pendidikan',
                'Mengawas Ujian Nasional Tingkat Kota Semarang',
                'Mendampingi Siswa Lomba Olimpiade Fisika',
                'Rapat Koordinasi MGMP Se-Kota Semarang',
                'Menghadiri Pengajian Akbar Yayasan Wakaf Sultan Agung',
            ];
            Letter::create([
                'template_id' => $template2->id,
                'letter_number' => '090/' . str_pad($i + 6, 3, '0', STR_PAD_LEFT) . '/SMA-SA1/VI/' . date('Y'),
                'field_values' => [
                    'employee_name' => $employee->user->name ?? 'Karyawan',
                    'employee_number' => $employee->employee_number,
                    'position' => $employee->position,
                    'task_description' => $tasks[$i],
                    'task_date' => now()->addDays(rand(1, 5))->translatedFormat('d F Y'),
                ],
                'employee_id' => $employee->id,
                'created_by' => $stafTu->id,
                'issue_date' => now()->subDays(rand(1, 5))->format('Y-m-d'),
                'status' => 'issued',
                'notes' => 'Surat tugas dinas sekolah.',
            ]);
        }

        // 5 Surat Undangan Wali Murid (student_id)
        for ($i = 0; $i < 5; $i++) {
            $student = $students[$i + 5] ?? $students->first();
            Letter::create([
                'template_id' => $template3->id,
                'letter_number' => '005/' . str_pad($i + 11, 3, '0', STR_PAD_LEFT) . '/SMA-SA1/VI/' . date('Y'),
                'field_values' => [
                    'student_name' => $student->user->name ?? 'Siswa',
                    'classroom' => $student->classroom->name ?? 'X-A',
                    'event_name' => 'Rapat Pembahasan Anggaran Sekolah',
                    'event_date' => now()->addDays(7)->translatedFormat('d F Y'),
                    'event_location' => 'Aula Utama SMA Islam Sultan Agung 1',
                ],
                'student_id' => $student->id,
                'created_by' => $stafTu->id,
                'issue_date' => now()->subDays(rand(1, 3))->format('Y-m-d'),
                'status' => 'issued',
                'notes' => 'Surat undangan Wali Murid.',
            ]);
        }
    }
}
