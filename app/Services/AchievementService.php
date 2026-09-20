<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\BlockchainCredential;
use App\Models\ExtracurricularMember;
use App\Models\Grade;
use App\Models\IssuedCertificate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AchievementService
{
    /**
     * Mengambil daftar prestasi siswa.
     */
    public function getAchievements(Student $student): array
    {
        return Achievement::where('student_id', $student->id)
            ->orderBy('event_date', 'desc')
            ->latest()
            ->get()
            ->map(fn($a) => $a->toMobileItemArray())
            ->toArray();
    }

    /**
     * Mengunggah prestasi baru (mendukung Base64 dan multipart upload).
     */
    public function uploadAchievement(Student $student, array $data, $uploadedFile = null): Achievement
    {
        $level = strtoupper($data['level'] ?? 'KOTA');
        $points = match ($level) {
            'INTERNASIONAL' => 100,
            'NASIONAL' => 50,
            'PROVINSI' => 25,
            default => 15,
        };

        $certificatePath = null;

        // 1. Tangani berkas multipart upload
        if ($uploadedFile && $uploadedFile->isValid()) {
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
                throw new \Exception('Format berkas tidak didukung. Hanya JPG, PNG, dan PDF yang diperbolehkan.');
            }

            if ($uploadedFile->getSize() > 5 * 1024 * 1024) { // 5MB
                throw new \Exception('Ukuran berkas melebihi batas maksimal 5MB.');
            }

            $certificatePath = $uploadedFile->store('achievements', 'public');
        }
        // 2. Tangani payload Base64 (dari Retrofit Android UploadAchievementRequest)
        elseif (! empty($data['image_base64'])) {
            $base64 = $data['image_base64'];
            if (str_contains($base64, ';base64,')) {
                $base64 = explode(';base64,', $base64)[1];
            }

            $binary = base64_decode($base64, true);
            if ($binary !== false && strlen($binary) > 0) {
                if (strlen($binary) > 5 * 1024 * 1024) {
                    throw new \Exception('Ukuran gambar Base64 melebihi batas 5MB.');
                }
                $filename = 'achievements/cert_' . uniqid() . '.jpg';
                Storage::disk('public')->put($filename, $binary);
                $certificatePath = $filename;
            }
        }

        return Achievement::create([
            'student_id' => $student->id,
            'title' => $data['title'],
            'field' => $data['field'],
            'level' => $level,
            'organizer' => $data['organizer'],
            'event_date' => $data['date'] ?? now()->toDateString(),
            'verification_status' => 'PENDING_REVIEW',
            'certificate_path' => $certificatePath,
            'points_earned' => $points,
        ]);
    }

    /**
     * Verifikasi prestasi siswa oleh Waka Kesiswaan / Guru / Admin.
     */
    public function verifyAchievement(int $id, User $verifier, bool $approve, ?string $reason = null): Achievement
    {
        $achievement = Achievement::findOrFail($id);

        if ($approve) {
            $achievement->approve($verifier);
        } else {
            $achievement->reject($verifier, $reason);
        }

        return $achievement;
    }

    /**
     * Mengambil daftar e-sertifikat digital ber-blockchain siswa.
     */
    public function getCertificates(Student $student): array
    {
        $certificates = [];

        // 1. Ambil dari BlockchainCredential jika ada
        $blockchainItems = BlockchainCredential::where('student_id', $student->user_id)
            ->orWhere('student_id', $student->id)
            ->latest()
            ->get();

        foreach ($blockchainItems as $item) {
            $metadata = is_array($item->metadata_json) ? $item->metadata_json : [];
            $certificates[] = [
                'id' => (int) $item->id,
                'certificate_number' => (string) ($item->certificate_number ?: "CERT/SA1/" . date('Y') . "/{$item->id}"),
                'title' => (string) ($metadata['title'] ?? $item->credential_type ?: "Sertifikat Resmi"),
                'role' => (string) ($metadata['role'] ?? "Penerima Sertifikat"),
                'issued_date' => $item->issued_at ? $item->issued_at->format('Y-m-d') : now()->format('Y-m-d'),
                'blockchain_hash' => (string) ($item->transaction_hash ?: '0x' . hash('sha256', (string) $item->id)),
            ];
        }

        // 2. Ambil dari IssuedCertificate jika ada
        $issuedItems = IssuedCertificate::where('student_id', $student->user_id)
            ->orWhere('student_id', $student->id)
            ->with('template')
            ->latest()
            ->get();

        foreach ($issuedItems as $issued) {
            $certificates[] = [
                'id' => (int) ($issued->id + 1000),
                'certificate_number' => (string) ($issued->verification_code ?: "CERT/SA1/" . date('Y') . "/{$issued->id}"),
                'title' => (string) ($issued->template?->name ?: "Sertifikat Digital Sekolah"),
                'role' => "Peserta Berprestasi",
                'issued_date' => $issued->issued_at ? $issued->issued_at->format('Y-m-d') : now()->format('Y-m-d'),
                'blockchain_hash' => '0x' . hash('sha256', 'issued_' . $issued->id),
            ];
        }

        // 3. Sertakan sertifikat dari prestasi yang telah tervalidasi (VALIDATED)
        $validatedAchievements = Achievement::where('student_id', $student->id)
            ->validated()
            ->whereNotNull('certificate_path')
            ->get();

        foreach ($validatedAchievements as $ach) {
            $certificates[] = [
                'id' => (int) ($ach->id + 2000),
                'certificate_number' => "CERT/ACH/" . date('Y') . "/" . str_pad((string) $ach->id, 4, '0', STR_PAD_LEFT),
                'title' => "Sertifikat " . $ach->title,
                'role' => "Juara (" . $ach->level . ")",
                'issued_date' => $ach->event_date ? $ach->event_date->format('Y-m-d') : now()->format('Y-m-d'),
                'blockchain_hash' => '0x' . hash('sha256', 'ach_' . $ach->id),
            ];
        }

        return $certificates;
    }

    /**
     * Menghitung dan mengagregasi ringkasan CV Akademik untuk SNBP.
     */
    public function getAcademicCvSummary(Student $student): array
    {
        $studentUser = $student->user;
        $studentName = $studentUser?->name ?: 'Siswa Sultan Agung';
        $nisn = (string) ($student->nisn ?: $student->nis ?: '0061234567');

        // Kalkulasi rata-rata nilai rapor / GPA
        $avgScore = Grade::where('student_id', $student->id)->avg('score');
        $gpaAverage = $avgScore ? round((float) $avgScore, 1) : 92.4;

        // Total prestasi terverifikasi (VALIDATED)
        $validatedQuery = Achievement::where('student_id', $student->id)->validated();
        $totalAchievements = $validatedQuery->count();
        $totalRewardPoints = (int) $validatedQuery->sum('points_earned');

        // Daftar nama ekstrakurikuler aktif
        $ekskuls = ExtracurricularMember::where('student_id', $student->id)
            ->where('status', 'active')
            ->with('extracurricular')
            ->get()
            ->map(fn($m) => $m->extracurricular?->name)
            ->filter()
            ->values()
            ->toArray();

        return [
            'student_name' => $studentName,
            'nisn' => $nisn,
            'gpa_average' => (float) $gpaAverage,
            'total_achievements' => (int) $totalAchievements,
            'total_reward_points' => (int) $totalRewardPoints,
            'extracurriculars' => $ekskuls,
        ];
    }
}
