<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\ReportCardCharacter;
use App\Models\ReportCardEntry;
use App\Models\ReportCardPdf;
use App\Models\ReportCardTemplate;
use App\Models\Student;
use App\Models\User;
use App\Services\Academic\ERaporService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ERaporPdfService
{
    public function __construct(private readonly ?ERaporService $eRaporService = null) {}

    public function calculatePredikat(float $score): string
    {
        if ($score >= 90) {
            return 'A';
        }
        if ($score >= 80) {
            return 'B';
        }
        if ($score >= 70) {
            return 'C';
        }

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

    public function signedFileUrl(ReportCardPdf $pdf): ?string
    {
        if (! $pdf->file_path) {
            return null;
        }

        return URL::temporarySignedRoute('api.v1.rapor.files.show', now()->addHours(6), [
            'pdf' => $pdf->id,
        ]);
    }

    public function getStudentReport(int $studentId, ?int $templateId = null): array
    {
        $template = $templateId
            ? ReportCardTemplate::with('academicYear')->find($templateId)
            : ReportCardTemplate::with('academicYear')->where('is_published', true)->latest()->first();

        if (! $template) {
            $template = ReportCardTemplate::with('academicYear')->latest()->first();
        }

        $student = User::find($studentId);
        $studentProfile = Student::with('classroom')->where('user_id', $studentId)->first();

        $entries = ReportCardEntry::with('subject')
            ->where('student_id', $studentId)
            ->when($template, fn ($q) => $q->where('report_card_id', $template->id))
            ->get()
            ->each(function (ReportCardEntry $entry): void {
                $entry->setAttribute('subject_name', $entry->subject?->name);
            });

        $characters = ReportCardCharacter::where('student_id', $studentId)
            ->when($template, fn ($q) => $q->where('report_card_id', $template->id))
            ->get();

        $latestPdf = ReportCardPdf::where('student_id', $studentId)
            ->when($template, fn ($q) => $q->where('report_card_id', $template->id))
            ->latest()
            ->first();

        $averageScore = $entries->count() > 0 ? round($entries->avg('score'), 2) : 0;

        return [
            'template' => $template,
            'student' => [
                'id' => $student->id ?? $studentId,
                'name' => $student->name ?? 'Siswa SISTA',
                'nisn' => $studentProfile->nisn ?? null,
                'class_name' => $studentProfile->classroom->name ?? null,
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
                'is_official' => (bool) $latestPdf->is_official,
                'file_path' => $this->signedFileUrl($latestPdf),
                'sha256_hash' => $latestPdf->sha256_hash,
                'generated_at' => $latestPdf->generated_at ? $latestPdf->generated_at->toIso8601String() : null,
            ] : [
                'is_ready' => false,
                'is_official' => false,
                'file_path' => null,
                'sha256_hash' => null,
                'generated_at' => null,
            ],
        ];
    }

    public function generateSinglePdf(int $reportCardId, int $studentId, int $generatedBy): ReportCardPdf
    {
        $existingOfficial = ReportCardPdf::where('report_card_id', $reportCardId)
            ->where('student_id', $studentId)
            ->where('is_official', true)
            ->first();

        if ($existingOfficial && $existingOfficial->file_path && Storage::disk('local')->exists($existingOfficial->file_path)) {
            return $existingOfficial;
        }

        $template = ReportCardTemplate::with('academicYear')->find($reportCardId);
        $studentProfile = Student::with(['user', 'classroom.teacher'])->where('user_id', $studentId)->first();
        $academicYear = $template?->academicYear;

        $eRapor = $this->eRaporService ?? app(ERaporService::class);
        $grades = ($studentProfile && $academicYear)
            ? Grade::with('subject')
                ->where('student_id', $studentProfile->id)
                ->where('academic_year_id', $academicYear->id)
                ->get()
            : collect();

        $reportCard = ($studentProfile && $academicYear)
            ? ReportCard::where('student_id', $studentProfile->id)
                ->where('academic_year_id', $academicYear->id)
                ->first()
            : null;

        $attendance = ($studentProfile && $academicYear)
            ? (object) $eRapor->calculateDayLevelAttendance($studentProfile, $academicYear)
            : (object) ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

        $officials = $studentProfile?->classroom
            ? $eRapor->resolveOfficials($studentProfile->classroom, $template?->issued_date)
            : ['wali_kelas' => null, 'principal' => null, 'principal_name' => null, 'principal_nip' => null];

        $studentForView = $studentProfile ?? new Student(['nis' => '-', 'nisn' => '-']);
        if (! $studentProfile) {
            $user = User::find($studentId);
            $studentForView->setRelation('user', $user);
        }

        $pdfBinary = Pdf::loadView('report-cards.pdf', [
            'student' => $studentForView,
            'academicYear' => $academicYear ?? (object) ['name' => '-'],
            'reportCard' => $reportCard,
            'grades' => $grades,
            'attendance' => $attendance,
            'waliKelas' => $officials['wali_kelas'],
            'kepalaSekolah' => $officials['principal'] ?? (object) ['name' => $officials['principal_name'] ?? '............................................'],
            'template' => $template,
            'isPreview' => true,
        ])->setPaper('A4', 'portrait')->output();

        $relativePath = "erapor-pdfs/{$reportCardId}/preview_{$studentId}.pdf";
        Storage::disk('local')->put($relativePath, $pdfBinary);

        $sha256 = hash('sha256', $pdfBinary);

        return ReportCardPdf::updateOrCreate(
            [
                'report_card_id' => $reportCardId,
                'student_id' => $studentId,
            ],
            [
                'file_path' => $relativePath,
                'sha256_hash' => $sha256,
                'qr_verification_url' => url("/api/v1/rapor/verify/{$sha256}"),
                'is_official' => false,
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
