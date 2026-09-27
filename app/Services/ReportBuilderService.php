<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportBuilderService
{
    /**
     * Kamus Sumber Data dan Kolom Terkait (Data Dictionary)
     */
    public function getAvailableSources(): array
    {
        return [
            'students' => [
                'label' => 'Data Siswa & Profil',
                'model' => Student::class,
                'columns' => [
                    'nis' => ['label' => 'NIS', 'field' => 'students.nis', 'type' => 'string'],
                    'nisn' => ['label' => 'NISN', 'field' => 'students.nisn', 'type' => 'string'],
                    'name' => ['label' => 'Nama Siswa', 'field' => 'users.name', 'type' => 'string'],
                    'email' => ['label' => 'Email', 'field' => 'users.email', 'type' => 'string'],
                    'classroom_name' => ['label' => 'Kelas', 'field' => 'classrooms.name', 'type' => 'string'],
                    'gender' => ['label' => 'Jenis Kelamin', 'field' => 'students.gender', 'type' => 'string'],
                    'status' => ['label' => 'Status Aktif', 'field' => 'students.status', 'type' => 'string'],
                    'address' => ['label' => 'Alamat', 'field' => 'students.address', 'type' => 'string'],
                ],
            ],
            'billings' => [
                'label' => 'Keuangan & Tagihan SPP',
                'model' => Billing::class,
                'columns' => [
                    'student_name' => ['label' => 'Nama Siswa', 'field' => 'users.name', 'type' => 'string'],
                    'classroom_name' => ['label' => 'Kelas', 'field' => 'classrooms.name', 'type' => 'string'],
                    'category_name' => ['label' => 'Jenis Tagihan', 'field' => 'payment_categories.name', 'type' => 'string'],
                    'amount' => ['label' => 'Total Tagihan', 'field' => 'billings.amount', 'type' => 'numeric'],
                    'remaining_balance' => ['label' => 'Sisa Tagihan', 'field' => 'billings.remaining_balance', 'type' => 'numeric'],
                    'due_date' => ['label' => 'Jatuh Tempo', 'field' => 'billings.due_date', 'type' => 'date'],
                    'status' => ['label' => 'Status Bayar', 'field' => 'billings.status', 'type' => 'string'],
                ],
            ],
            'attendances' => [
                'label' => 'Kehadiran & Absensi',
                'model' => Attendance::class,
                'columns' => [
                    'student_name' => ['label' => 'Nama Siswa', 'field' => 'users.name', 'type' => 'string'],
                    'classroom_name' => ['label' => 'Kelas', 'field' => 'classrooms.name', 'type' => 'string'],
                    'date' => ['label' => 'Tanggal Absen', 'field' => 'attendances.date', 'type' => 'date'],
                    'status' => ['label' => 'Status Kehadiran', 'field' => 'attendances.status', 'type' => 'string'],
                    'notes' => ['label' => 'Catatan', 'field' => 'attendances.notes', 'type' => 'string'],
                ],
            ],
            'grades' => [
                'label' => 'Akademik & Nilai Siswa',
                'model' => Grade::class,
                'columns' => [
                    'student_name' => ['label' => 'Nama Siswa', 'field' => 'users.name', 'type' => 'string'],
                    'classroom_name' => ['label' => 'Kelas', 'field' => 'classrooms.name', 'type' => 'string'],
                    'subject_name' => ['label' => 'Mata Pelajaran', 'field' => 'subjects.name', 'type' => 'string'],
                    'type' => ['label' => 'Kategori Nilai', 'field' => 'grades.type', 'type' => 'string'],
                    'score' => ['label' => 'Nilai Angka', 'field' => 'grades.score', 'type' => 'numeric'],
                    'description' => ['label' => 'Keterangan', 'field' => 'grades.description', 'type' => 'string'],
                ],
            ],
        ];
    }

    /**
     * Eksekusi Query Dinamis Berdasarkan Konfigurasi Laporan
     */
    public function executeQuery(array $config): Collection
    {
        $sourceKey = $config['source'] ?? 'students';
        $columns = $config['columns'] ?? [];
        $filters = $config['filters'] ?? [];
        $groupBy = $config['groupBy'] ?? null;
        $orderBy = $config['orderBy'] ?? null;
        $orderDir = $config['orderDir'] ?? 'asc';

        $availableSources = $this->getAvailableSources();
        if (! isset($availableSources[$sourceKey])) {
            throw new \InvalidArgumentException('Sumber data tidak valid.');
        }

        // Tentukan Model dan Mapping Kolom
        $sourceConfig = $availableSources[$sourceKey];
        $query = DB::table($sourceKey);

        // Terapkan JOIN dan SELECT berdasarkan Sumber Data
        if ($sourceKey === 'students') {
            $query->join('users', 'students.user_id', '=', 'users.id')
                ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                ->whereNull('students.deleted_at');
        } elseif ($sourceKey === 'billings') {
            $query->join('students', 'billings.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                ->join('payment_categories', 'billings.payment_category_id', '=', 'payment_categories.id')
                ->whereNull('billings.deleted_at');
        } elseif ($sourceKey === 'attendances') {
            $query->join('students', 'attendances.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id');
        } elseif ($sourceKey === 'grades') {
            $query->join('students', 'grades.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
                ->whereNull('grades.deleted_at');
        }

        // Select kolom terpilih secara aman menggunakan mapping field asli
        $selects = [];
        foreach ($columns as $colKey) {
            if (isset($sourceConfig['columns'][$colKey])) {
                $field = $sourceConfig['columns'][$colKey]['field'];
                $selects[] = "{$field} as {$colKey}";
            }
        }
        if (! empty($selects)) {
            $query->selectRaw(implode(', ', $selects));
        }

        // Terapkan Filter Dinamis
        foreach ($filters as $filter) {
            $colKey = $filter['column'] ?? null;
            $operator = strtolower($filter['operator'] ?? '=');
            $val = $filter['value'] ?? '';

            if ($colKey && isset($sourceConfig['columns'][$colKey])) {
                $field = $sourceConfig['columns'][$colKey]['field'];

                $allowedOperators = ['=', '>', '<', '>=', '<=', '!=', 'like', 'in'];
                if (! in_array($operator, $allowedOperators, true)) {
                    continue;
                }

                if ($operator === 'like') {
                    $query->where($field, 'like', "%{$val}%");
                } elseif ($operator === 'in') {
                    $query->whereIn($field, array_map('trim', explode(',', $val)));
                } else {
                    $query->where($field, $operator, $val);
                }
            }
        }

        // Terapkan Group By
        if ($groupBy && isset($sourceConfig['columns'][$groupBy])) {
            $query->groupBy($sourceConfig['columns'][$groupBy]['field']);
        }

        // Terapkan Order By
        if ($orderBy && isset($sourceConfig['columns'][$orderBy])) {
            $query->orderBy($sourceConfig['columns'][$orderBy]['field'], $orderDir);
        }

        return $query->get();
    }
}
