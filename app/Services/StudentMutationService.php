<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentMutation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Class StudentMutationService
 *
 * Menangani proses mutasi (pindah masuk/keluar) siswa beserta manajemen
 * dokumen pendukungnya.
 */
class StudentMutationService
{
    /**
     * Process a new student mutation.
     *
     * Memproses permohonan mutasi dalam transaksi database. Jika terdapat dokumen,
     * file akan diunggah ke storage. Jika mutasi berjenis 'out' (keluar), status
     * siswa akan diubah menjadi 'mutated'.
     *
     * @param  array  $validatedData  Data mutasi yang sudah divalidasi
     * @param  UploadedFile|null  $document  File dokumen pendukung (opsional)
     * @return StudentMutation Model mutasi yang berhasil dibuat
     *
     * @throws ModelNotFoundException Jika siswa tidak ditemukan
     */
    public function processMutation(array $validatedData, ?UploadedFile $document): StudentMutation
    {
        return DB::transaction(function () use ($validatedData, $document) {
            $student = Student::findOrFail($validatedData['student_id']);

            $docPath = null;
            if ($document) {
                $docPath = $document->store('mutations', 'public');
            }

            $mutation = StudentMutation::create(array_merge($validatedData, [
                'document_path' => $docPath,
            ]));

            // Update status if mutated out
            if ($validatedData['type'] === 'out') {
                $student->update(['status' => 'mutated']);
            }

            return $mutation;
        });
    }
}
