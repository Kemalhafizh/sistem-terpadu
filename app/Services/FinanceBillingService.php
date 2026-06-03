<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\Student;

/**
 * Class FinanceBillingService
 *
 * Layanan untuk mengelola pembuatan tagihan (billing) massal
 * bagi siswa berdasarkan kategori pembayaran dan tahun ajaran aktif.
 */
class FinanceBillingService
{
    /**
     * Bulk generate billings for all students.
     *
     * Membuat tagihan baru untuk seluruh siswa aktif berdasarkan kategori pembayaran.
     * Metode ini memastikan tidak ada tagihan duplikat (idempoten) berdasarkan
     * kombinasi student_id, payment_category_id, academic_year_id, dan month.
     *
     * @param  array  $validatedData  Data tagihan yang telah divalidasi (kategori, jumlah, tanggal jatuh tempo)
     * @return int Jumlah tagihan (billing) yang berhasil digenerate dan disimpan ke database
     */
    public function generateBulk(array $validatedData): int
    {
        // Optimize: Get all student IDs who ALREADY have this billing in one query
        $existingStudentIds = Billing::where('payment_category_id', $validatedData['payment_category_id'])
            ->where('academic_year_id', $validatedData['academic_year_id'])
            ->where('month', $validatedData['month'] ?? null)
            ->pluck('student_id')
            ->toArray();

        // Get students who DON'T have this billing yet
        $studentsToBill = Student::whereNotIn('id', $existingStudentIds)->get();

        $count = 0;
        foreach ($studentsToBill as $student) {
            Billing::create(array_merge($validatedData, [
                'student_id' => $student->id,
                'status' => 'unpaid',
            ]));
            $count++;
        }

        return $count;
    }
}
