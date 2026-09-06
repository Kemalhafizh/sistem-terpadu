<?php

namespace App\Services;

use App\Models\ReportCardTemplate;
use App\Models\ReportCardEntry;
use App\Models\ReportCardCharacter;
use App\Models\ReportCardPdf;
use App\Models\User;
use Illuminate\Support\Str;

class ERaporPdfService
{
    public function calculatePredikat(float $score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        return 'D';
    }

    public function generateNarasiKktp(float $score, string $subjectName): string
    {
        if ($score >= 90) {
            return "Menunjukkan penguasaan yang sangat baik dalam seluruh tujuan pembelajaran mata pelajaran {$subjectName}, memiliki nalar kritis dan pemahaman konsep yang mendalam.";
        } elseif ($score >= 80) {
            return "Menunjukkan penguasaan yang baik dalam memahami konsep dasar dan penerapan materi {$subjectName}, perlu sedikit peningkatan pada penyelesaian studi kasus kompleks.";
        } elseif ($score >= 70) {
            return "Mencapai kriteria ketuntasan minimal pada mata pelajaran {$subjectName}, perlu bimbingan berkelanjutan dan penguatan literasi konsep inti.";
        } else {
            return "Perlu pendampingan intensif dan remedial untuk mencapai kriteria ketercapaian tujuan pembelajaran pada mata pelajaran {$subjectName}.";
        }
    }

    public function getStudentReport(int $studentId, ?int $templateId = null): array
    {
        $template = $templateId 
            ? ReportCardTemplate::with('academicYear')->find($templateId)
            : ReportCardTemplate::with('academicYear')->where('is_published', true)->latest()->first();

        if (!$template) {
            $template = ReportCardTemplate::with('academicYear')->latest()->first();
        }

        $student = User::find($studentId);

        $entries = ReportCardEntry::with('subject')
            ->where('student_id', $studentId)
            ->when($template, fn($q) => $q->where('report_card_id', $template->id))
            ->get();

        $characters = ReportCardCharacter::where('student_id', $studentId)
            ->when($template, fn($q) => $q->where('report_card_id', $template->id))
            ->get();

        $latestPdf = ReportCardPdf::where('student_id', $studentId)
            ->when($template, fn($q) => $q->where('report_card_id', $template->id))
            ->latest()
            ->first();

        $averageScore = $entries->count() > 0 ? round($entries->avg('score'), 2) : 0;

        return [
            'template' => $template,
            'student' => [
                'id' => $student->id ?? $studentId,
                'name' => $student->name ?? 'Siswa SISTA',
                'nisn' => $student->nisn ?? '1058291048',
                'class_name' => $student->class_name ?? 'XII MIPA 1',
            ],
            'academic_summary' => [
                'average_score' => $averageScore,
                'predikat_umum' => $this->calculatePredikat($averageScore),
                'total_subjects' => $entries->count(),
            ],
            'entries' => $entries,
            'characters' => $characters,
            'pdf_status' => $latestPdf ? [
                'is_ready' => true,
                'file_path' => $latestPdf->file_path,
                'sha256_hash' => $latestPdf->sha256_hash,
                'generated_at' => $latestPdf->generated_at ? $latestPdf->generated_at->toIso8601String() : null,
            ] : [
                'is_ready' => false,
                'file_path' => null,
                'sha256_hash' => null,
                'generated_at' => null,
            ],
        ];
    }

    public function generateSinglePdf(int $reportCardId, int $studentId, int $generatedBy): ReportCardPdf
    {
        $hashString = "SISTA-RAPOR-{$reportCardId}-{$studentId}-" . time();
        $sha256 = hash('sha256', $hashString);
        $fileName = "rapor_smk_sultan_agung_1_{$studentId}_" . time() . ".pdf";
        $filePath = "uploads/rapor/{$fileName}";

        return ReportCardPdf::updateOrCreate(
            [
                'report_card_id' => $reportCardId,
                'student_id' => $studentId,
            ],
            [
                'file_path' => $filePath,
                'sha256_hash' => $sha256,
                'qr_verification_url' => url("/api/v1/rapor/verify/{$sha256}"),
                'generated_at' => now(),
                'generated_by' => $generatedBy,
            ]
        );
    }

    public function verifyPdfHash(string $hash): ?ReportCardPdf
    {
        return ReportCardPdf::with(['template.academicYear', 'student'])
            ->where('sha256_hash', $hash)
            ->first();
    }
}
