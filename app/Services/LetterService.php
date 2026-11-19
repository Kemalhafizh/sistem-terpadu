<?php

namespace App\Services;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LetterService
{
    /**
     * Menghasilkan nomor urut surat otomatis berikutnya
     */
    public function generateLetterNumber(LetterTemplate $template, string $issueDate): string
    {
        $date = new \DateTime($issueDate);
        $year = $date->format('Y');
        $month = (int) $date->format('n');

        // Konversi bulan ke romawi
        $romans = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $romanMonth = $romans[$month] ?? 'I';

        // Hitung total surat yang pernah bernomor resmi pada tahun tersebut untuk template ini
        $count = Letter::withTrashed()
            ->where('template_id', $template->id)
            ->whereYear('issue_date', $year)
            ->whereNotNull('letter_number')
            ->count();

        $sequence = $count + 1;
        do {
            $seq = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $candidate = "{$seq}/{$template->code}/SMA-ISA/{$romanMonth}/{$year}";
            $sequence++;
        } while (Letter::withTrashed()->where('letter_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Me-render template surat dengan mengganti seluruh placeholders dinamis
     */
    public function renderLetterBody(Letter $letter): string
    {
        $letter->loadMissing(['template', 'student.user', 'student.classroom', 'employee.user']);
        $template = $letter->template;
        $body = $template->body_template;

        // Kumpulkan data placeholders standar
        $replacements = [];

        // Masukkan data siswa jika terikat
        if ($letter->student) {
            $student = $letter->student;
            $replacements['{{nama_siswa}}'] = $student->name;
            $replacements['{{nis}}'] = $student->nis;
            $replacements['{{nisn}}'] = $student->nisn;
            $replacements['{{kelas}}'] = $student->classroom?->name ?? '-';
            $replacements['{{tempat_lahir}}'] = $student->birth_place ?? '-';
            $replacements['{{tanggal_lahir}}'] = $student->birth_date ? (Carbon::parse($student->birth_date)->format('d-m-Y')) : '-';
        }

        // Masukkan data pegawai jika terikat
        if ($letter->employee) {
            $employee = $letter->employee;
            $replacements['{{nama_pegawai}}'] = $employee->name;
            $replacements['{{nip}}'] = $employee->nip ?? '-';
            $replacements['{{jabatan}}'] = $employee->position ?? '-';
        }

        // Masukkan data dinamis kustom dari form JSON
        if (is_array($letter->field_values)) {
            foreach ($letter->field_values as $key => $val) {
                $replacements['{{'.$key.'}}'] = is_scalar($val) ? (string) $val : '';
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $body);
    }

    /**
     * Menghasilkan file PDF berkualitas cetak dengan DomPDF
     */
    public function generatePdf(Letter $letter): \Barryvdh\DomPDF\PDF
    {
        $letter->loadMissing(['template', 'student.user', 'student.classroom', 'employee.user', 'approver']);
        $renderedBody = $this->renderLetterBody($letter);
        $template = $letter->template;

        // Base64 Signature Image if exists
        $signatureBase64 = null;
        if ($template->signature_path && Storage::disk('local')->exists($template->signature_path)) {
            $fileData = Storage::disk('local')->get($template->signature_path);
            $mimeType = Storage::disk('local')->mimeType($template->signature_path);
            $signatureBase64 = 'data:'.$mimeType.';base64,'.base64_encode($fileData);
        }

        $verificationUrl = filled($letter->verification_token)
            ? route('letters.verify', $letter->verification_token)
            : null;

        $pdf = Pdf::loadView('letters.pdf', compact(
            'letter',
            'renderedBody',
            'template',
            'signatureBase64',
            'verificationUrl'
        ));
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Membuat surat massal untuk sekelompok siswa / satu kelas
     */
    public function bulkGenerate(LetterTemplate $template, array $studentIds, array $commonFields, int $creatorId): Collection
    {
        $letters = collect();

        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);
            if (! $student) {
                continue;
            }

            $letter = Letter::create([
                'template_id' => $template->id,
                'field_values' => $commonFields,
                'student_id' => $student->id,
                'created_by' => $creatorId,
                'issue_date' => now()->format('Y-m-d'),
                'status' => 'draft',
            ]);

            $letters->push($letter);
        }

        return $letters;
    }
}
